<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log_ga.txt');

/**
 * ALGORITMA GENETIKA - Penjadwalan Mata Pelajaran
 * SMAN 2 Toraja Utara
 * Versi 6 - FIX: fallback saat inisialisasi tidak lagi asal taruh slot/guru/ruang
 * secara acak (yang menyebabkan bentrok tetap "terkunci" oleh elitism sampai
 * generasi terakhir). Ditambahkan juga fungsi repair_konflik() yang dijalankan
 * setelah evolusi GA selesai, untuk memindahkan gen yang masih bentrok ke slot
 * yang benar-benar kosong sebelum disimpan ke database.
 */

include 'koneksi.php';
header('Content-Type: application/json');
set_time_limit(600);

// =============================================
// 1. AMBIL PARAMETER
// =============================================
$pop_size   = isset($_POST['pop_size'])   ? (int)$_POST['pop_size']     : 50;
$max_gen    = isset($_POST['max_gen'])    ? (int)$_POST['max_gen']      : 200;
$cross_rate = isset($_POST['cross_rate']) ? (float)$_POST['cross_rate'] : 0.8;
$mut_rate   = isset($_POST['mut_rate'])   ? (float)$_POST['mut_rate']   : 0.1;

// =============================================
// 2. AMBIL DATA DARI DATABASE
// =============================================
$semester = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM semester WHERE status='1' LIMIT 1"));
if (!$semester) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada semester aktif!']);
    exit;
}
$id_semester = $semester['id_semester'];

$kelas_ids = [];
$ruang_ids = [];

// PENTING: setiap baris tabel jam SUDAH terikat ke hari tertentu
// (mis. id_jam=40 cuma valid untuk hari Kamis jam ke-2).
// Maka slot waktu yang valid = baris-baris tabel jam itu sendiri,
// BUKAN kombinasi bebas antara semua hari x semua id_jam.
$semua_slot_waktu = []; // tiap elemen: ['hari'=>.., 'id_jam'=>.., 'jam_ke'=>..]
$q = mysqli_query($conn, "SELECT id_jam, hari, jam FROM jam ORDER BY hari, jam ASC");
while ($r = mysqli_fetch_assoc($q)) {
    $semua_slot_waktu[] = [
        'hari'   => $r['hari'],
        'id_jam' => $r['id_jam'],
        'jam_ke' => $r['jam'],
    ];
}

$q = mysqli_query($conn, "SELECT id_kelas FROM kelas WHERE id_semester=$id_semester");
while ($r = mysqli_fetch_assoc($q)) $kelas_ids[] = $r['id_kelas'];

$q = mysqli_query($conn, "SELECT id_ruang FROM ruang_kelas");
while ($r = mysqli_fetch_assoc($q)) $ruang_ids[] = $r['id_ruang'];

// Ambil relasi guru -> mapel
$guru_mapel  = [];
$semua_mapel = [];

$q = mysqli_query($conn, "SELECT gp.id_guru, gp.id_pelajaran FROM guru_pelajaran gp INNER JOIN guru g ON gp.id_guru = g.id_guru");
while ($r = mysqli_fetch_assoc($q)) {
    $id_p = $r['id_pelajaran'];
    $id_g = $r['id_guru'];
    if (!isset($guru_mapel[$id_p])) $guru_mapel[$id_p] = [];
    $guru_mapel[$id_p][] = $id_g;
    if (!in_array($id_p, $semua_mapel)) $semua_mapel[] = $id_p;
}

$semua_guru = [];
$q = mysqli_query($conn, "SELECT id_guru FROM guru");
while ($r = mysqli_fetch_assoc($q)) $semua_guru[] = $r['id_guru'];

if (empty($semua_mapel)) {
    $q = mysqli_query($conn, "SELECT id_pelajaran FROM pelajaran");
    while ($r = mysqli_fetch_assoc($q)) $semua_mapel[] = $r['id_pelajaran'];
    foreach ($semua_mapel as $id_p) $guru_mapel[$id_p] = $semua_guru;
}

// =============================================
// 2b. AMBIL JAM_PER_MINGGU PER KELAS DARI TABEL kelas_pelajaran
// =============================================
$kelas_pelajaran = []; // id_kelas => [ ['id_pelajaran'=>.., 'jam_per_minggu'=>..], ... ]
$q = mysqli_query($conn, "SELECT id_kelas, id_pelajaran, jam_per_minggu FROM kelas_pelajaran WHERE jam_per_minggu > 0");
while ($r = mysqli_fetch_assoc($q)) {
    $kelas_pelajaran[$r['id_kelas']][] = [
        'id_pelajaran'   => $r['id_pelajaran'],
        'jam_per_minggu' => (int)$r['jam_per_minggu'],
    ];
}

if (empty($kelas_ids) || empty($semua_slot_waktu) || empty($ruang_ids) || empty($semua_mapel)) {
    echo json_encode(['success' => false, 'message' => 'Data master tidak lengkap!']);
    exit;
}

if (empty($kelas_pelajaran)) {
    echo json_encode(['success' => false, 'message' => 'Data kurikulum (kelas_pelajaran / jam_per_minggu) belum diisi! Silakan atur jam per minggu tiap mapel di menu Kurikulum terlebih dahulu.']);
    exit;
}

// =============================================
// 3. FUNGSI BANTU
// =============================================

function get_guru_for_mapel($id_pelajaran, $guru_mapel, $semua_guru) {
    if (isset($guru_mapel[$id_pelajaran]) && !empty($guru_mapel[$id_pelajaran])) {
        return $guru_mapel[$id_pelajaran];
    }
    return $semua_guru;
}

function bangun_daftar_instance_mapel($id_kelas, $kelas_pelajaran) {
    $instance_list = [];
    if (!isset($kelas_pelajaran[$id_kelas])) return $instance_list;

    foreach ($kelas_pelajaran[$id_kelas] as $mp) {
        for ($i = 0; $i < $mp['jam_per_minggu']; $i++) {
            $instance_list[] = $mp['id_pelajaran'];
        }
    }
    return $instance_list;
}

/**
 * [BARU] Cari slot+guru+ruang yang benar-benar bebas, melihat KESELURUHAN
 * slot waktu yang tersedia (bukan cuma sebagian), dan kalau guru mapel asli
 * semuanya penuh, coba guru lain sebagai upaya terakhir (silang mapel darurat)
 * daripada dipaksa taruh guru yang sudah dipakai di jam yang sama.
 *
 * Return: ['hari'=>.., 'id_jam'=>.., 'id_guru'=>.., 'id_ruang'=>..] atau null
 * kalau memang benar-benar tidak ada slot kosong sama sekali untuk kelas ini
 * (kapasitas total sudah habis).
 */
function cari_slot_bebas($id_kelas, $id_pelajaran, $guru_mapel, $semua_guru, $ruang_ids,
                          $semua_slot_waktu, &$used_guru, &$used_ruang, &$used_kelas_slot) {

    $slot_acak = $semua_slot_waktu;
    shuffle($slot_acak);

    // Tahap 1: coba pakai guru pengampu resmi mapel ini
    $guru_utama = get_guru_for_mapel($id_pelajaran, $guru_mapel, $semua_guru);

    foreach ([$guru_utama, $semua_guru] as $daftar_guru) {
        // daftar_guru pertama = guru resmi mapel, kedua = SEMUA guru (darurat)
        foreach ($slot_acak as $slot) {
            $hari   = $slot['hari'];
            $id_jam = $slot['id_jam'];
            $key_kelas = $hari . '_' . $id_jam;

            if (!empty($used_kelas_slot[$key_kelas])) continue; // kelas ini sudah dipakai di jam ini

            $guru_list = $daftar_guru;
            shuffle($guru_list);
            $id_guru_ok = null;
            foreach ($guru_list as $ig) {
                $kg = $ig . '_' . $hari . '_' . $id_jam;
                if (empty($used_guru[$kg])) { $id_guru_ok = $ig; break; }
            }
            if ($id_guru_ok === null) continue;

            $ruang_acak = $ruang_ids;
            shuffle($ruang_acak);
            $id_ruang_ok = null;
            foreach ($ruang_acak as $ir) {
                $kr = $ir . '_' . $hari . '_' . $id_jam;
                if (empty($used_ruang[$kr])) { $id_ruang_ok = $ir; break; }
            }
            if ($id_ruang_ok === null) continue;

            return [
                'hari'     => $hari,
                'id_jam'   => $id_jam,
                'id_guru'  => $id_guru_ok,
                'id_ruang' => $id_ruang_ok,
            ];
        }
    }

    return null; // benar-benar tidak ada slot kosong untuk kelas ini
}

/**
 * SMART INITIALIZATION
 */
function buat_kromosom_smart($kelas_ids, $kelas_pelajaran, $guru_mapel, $semua_guru, $semua_slot_waktu, $ruang_ids, &$gagal_total) {
    $kromosom = [];

    $used_guru  = []; // "id_guru_hari_id_jam" => true
    $used_ruang = []; // "id_ruang_hari_id_jam" => true

    foreach ($kelas_ids as $id_kelas) {
        $slots_kelas = $semua_slot_waktu;
        shuffle($slots_kelas);

        $used_kelas_slot = []; // "hari_id_jam" => true

        $mapel_list = bangun_daftar_instance_mapel($id_kelas, $kelas_pelajaran);
        shuffle($mapel_list);

        $count_mapel_hari = []; // "id_pelajaran_hari" => jumlah

        foreach ($mapel_list as $id_pelajaran) {
            $assigned = false;

            // Coba dulu slot di hari yang belum ada mapel ini (sebar merata)
            foreach ([true, false] as $hindari_hari_sama) {
                if ($assigned) break;

                foreach ($slots_kelas as $slot) {
                    $hari   = $slot['hari'];
                    $id_jam = $slot['id_jam'];

                    $key_kelas = $hari . '_' . $id_jam;
                    if (isset($used_kelas_slot[$key_kelas])) continue;

                    if ($hindari_hari_sama) {
                        $key_mh = $id_pelajaran . '_' . $hari;
                        if (!empty($count_mapel_hari[$key_mh])) continue;
                    }

                    $guru_list = get_guru_for_mapel($id_pelajaran, $guru_mapel, $semua_guru);
                    shuffle($guru_list);
                    $id_guru_ok = null;
                    foreach ($guru_list as $id_guru) {
                        $key_guru = $id_guru . '_' . $hari . '_' . $id_jam;
                        if (!isset($used_guru[$key_guru])) {
                            $id_guru_ok = $id_guru;
                            break;
                        }
                    }
                    if ($id_guru_ok === null) continue;

                    $ruang_acak = $ruang_ids;
                    shuffle($ruang_acak);
                    $id_ruang_ok = null;
                    foreach ($ruang_acak as $id_ruang) {
                        $key_ruang = $id_ruang . '_' . $hari . '_' . $id_jam;
                        if (!isset($used_ruang[$key_ruang])) {
                            $id_ruang_ok = $id_ruang;
                            break;
                        }
                    }
                    if ($id_ruang_ok === null) continue;

                    $used_guru[$id_guru_ok . '_' . $hari . '_' . $id_jam]    = true;
                    $used_ruang[$id_ruang_ok . '_' . $hari . '_' . $id_jam]  = true;
                    $used_kelas_slot[$key_kelas] = true;
                    $count_mapel_hari[$id_pelajaran . '_' . $hari] =
                        ($count_mapel_hari[$id_pelajaran . '_' . $hari] ?? 0) + 1;

                    $kromosom[] = [
                        'id_kelas'     => $id_kelas,
                        'id_pelajaran' => $id_pelajaran,
                        'id_guru'      => $id_guru_ok,
                        'id_jam'       => $id_jam,
                        'id_ruang'     => $id_ruang_ok,
                        'hari'         => $hari,
                    ];
                    $assigned = true;
                    break;
                }
            }

            // [DIPERBAIKI] Fallback tidak lagi asal random.
            // Cari slot yang BENAR-BENAR bebas (kelas/guru/ruang) di seluruh
            // slot yang ada, kalau perlu pakai guru lain sebagai upaya darurat.
            if (!$assigned) {
                $hasil = cari_slot_bebas(
                    $id_kelas, $id_pelajaran, $guru_mapel, $semua_guru, $ruang_ids,
                    $semua_slot_waktu, $used_guru, $used_ruang, $used_kelas_slot
                );

                if ($hasil !== null) {
                    $used_guru[$hasil['id_guru'] . '_' . $hasil['hari'] . '_' . $hasil['id_jam']]   = true;
                    $used_ruang[$hasil['id_ruang'] . '_' . $hasil['hari'] . '_' . $hasil['id_jam']] = true;
                    $used_kelas_slot[$hasil['hari'] . '_' . $hasil['id_jam']] = true;

                    $kromosom[] = [
                        'id_kelas'     => $id_kelas,
                        'id_pelajaran' => $id_pelajaran,
                        'id_guru'      => $hasil['id_guru'],
                        'id_jam'       => $hasil['id_jam'],
                        'id_ruang'     => $hasil['id_ruang'],
                        'hari'         => $hasil['hari'],
                    ];
                } else {
                    // Kapasitas benar-benar habis (jumlah jam_per_minggu total
                    // kelas ini melebihi jumlah slot waktu yang ada).
                    // Taruh di slot manapun yang setidaknya tidak dobel-booking
                    // KELAS ini sendiri, supaya bentrok yang terjadi hanya di
                    // sisi guru/ruang (bukan menambah bentrok kelas juga),
                    // dan catat sebagai kegagalan struktural untuk dilaporkan.
                    $gagal_total++;
                    $slot_bebas_kelas = null;
                    foreach ($semua_slot_waktu as $slot) {
                        $key_kelas = $slot['hari'] . '_' . $slot['id_jam'];
                        if (empty($used_kelas_slot[$key_kelas])) { $slot_bebas_kelas = $slot; break; }
                    }
                    $slot = $slot_bebas_kelas ?? $semua_slot_waktu[array_rand($semua_slot_waktu)];
                    $guru_list = get_guru_for_mapel($id_pelajaran, $guru_mapel, $semua_guru);

                    $used_kelas_slot[$slot['hari'] . '_' . $slot['id_jam']] = true;

                    $kromosom[] = [
                        'id_kelas'     => $id_kelas,
                        'id_pelajaran' => $id_pelajaran,
                        'id_guru'      => $guru_list[array_rand($guru_list)],
                        'id_jam'       => $slot['id_jam'],
                        'id_ruang'     => $ruang_ids[array_rand($ruang_ids)],
                        'hari'         => $slot['hari'],
                    ];
                }
            }
        }
    }
    return $kromosom;
}

function hitung_fitness($kromosom) {
    return 1.0 / (1.0 + hitung_bentrok($kromosom));
}

function hitung_bentrok($kromosom) {
    $bentrok = 0;
    $seen_guru  = [];
    $seen_ruang = [];
    $seen_kelas = [];

    foreach ($kromosom as $gen) {
        $key_guru  = $gen['id_guru']  . '_' . $gen['id_jam'] . '_' . $gen['hari'];
        $key_ruang = $gen['id_ruang'] . '_' . $gen['id_jam'] . '_' . $gen['hari'];
        $key_kelas = $gen['id_kelas'] . '_' . $gen['id_jam'] . '_' . $gen['hari'];

        if (isset($seen_guru[$key_guru]))   $bentrok++;
        if (isset($seen_ruang[$key_ruang])) $bentrok++;
        if (isset($seen_kelas[$key_kelas])) $bentrok++;

        $seen_guru[$key_guru]   = true;
        $seen_ruang[$key_ruang] = true;
        $seen_kelas[$key_kelas] = true;
    }

    return $bentrok;
}

function seleksi_roulette($populasi, $fitness_list) {
    $total = array_sum($fitness_list);
    if ($total == 0) return $populasi[array_rand($populasi)];
    $r   = (mt_rand() / mt_getrandmax()) * $total;
    $kum = 0;
    foreach ($populasi as $i => $k) {
        $kum += $fitness_list[$i];
        if ($kum >= $r) return $k;
    }
    return end($populasi);
}

function crossover($p1, $p2, $cross_rate) {
    if ((mt_rand() / mt_getrandmax()) > $cross_rate) return $p1;
    $len = count($p1);
    if ($len < 2) return $p1;
    $titik = mt_rand(1, $len - 1);
    return array_merge(array_slice($p1, 0, $titik), array_slice($p2, $titik));
}

function mutasi_smart($kromosom, $mut_rate, $guru_mapel, $semua_guru, $ruang_ids, $semua_slot_waktu) {
    $used_guru  = [];
    $used_ruang = [];
    $used_kelas = [];
    foreach ($kromosom as $idx => $gen) {
        $kg = $gen['id_guru']  . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $kr = $gen['id_ruang'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $kk = $gen['id_kelas'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $used_guru[$kg][]  = $idx;
        $used_ruang[$kr][] = $idx;
        $used_kelas[$kk][] = $idx;
    }

    foreach ($kromosom as $idx => &$gen) {
        if ((mt_rand() / mt_getrandmax()) >= $mut_rate) continue;

        $kg_old = $gen['id_guru']  . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $kr_old = $gen['id_ruang'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $kk_old = $gen['id_kelas'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
        $used_guru[$kg_old]  = array_diff($used_guru[$kg_old],  [$idx]);
        $used_ruang[$kr_old] = array_diff($used_ruang[$kr_old], [$idx]);
        $used_kelas[$kk_old] = array_diff($used_kelas[$kk_old], [$idx]);

        $slot_acak = $semua_slot_waktu;
        shuffle($slot_acak);

        $found = false;
        foreach ($slot_acak as $slot) {
            $hari_baru = $slot['hari'];
            $jam_baru  = $slot['id_jam'];

            $kk_new = $gen['id_kelas'] . '_' . $hari_baru . '_' . $jam_baru;
            if (!empty($used_kelas[$kk_new])) continue;

            $guru_list = get_guru_for_mapel($gen['id_pelajaran'], $guru_mapel, $semua_guru);
            shuffle($guru_list);
            $id_guru_ok = null;
            foreach ($guru_list as $ig) {
                $kg_new = $ig . '_' . $hari_baru . '_' . $jam_baru;
                if (empty($used_guru[$kg_new])) { $id_guru_ok = $ig; break; }
            }
            if ($id_guru_ok === null) continue;

            $ruang_acak = $ruang_ids;
            shuffle($ruang_acak);
            $id_ruang_ok = null;
            foreach ($ruang_acak as $ir) {
                $kr_new = $ir . '_' . $hari_baru . '_' . $jam_baru;
                if (empty($used_ruang[$kr_new])) { $id_ruang_ok = $ir; break; }
            }
            if ($id_ruang_ok === null) continue;

            $gen['hari']    = $hari_baru;
            $gen['id_jam']  = $jam_baru;
            $gen['id_guru'] = $id_guru_ok;
            $gen['id_ruang']= $id_ruang_ok;

            $used_guru[$id_guru_ok . '_' . $hari_baru . '_' . $jam_baru][] = $idx;
            $used_ruang[$id_ruang_ok . '_' . $hari_baru . '_' . $jam_baru][] = $idx;
            $used_kelas[$kk_new][] = $idx;
            $found = true;
            break;
        }

        if (!$found) {
            $used_guru[$kg_old][]  = $idx;
            $used_ruang[$kr_old][] = $idx;
            $used_kelas[$kk_old][] = $idx;
        }
    }
    return $kromosom;
}

/**
 * [BARU] REPAIR PASCA-EVOLUSI
 * Dijalankan sekali di akhir, khusus untuk kromosom TERBAIK.
 * Cari semua gen yang masih menyebabkan bentrok (guru/ruang/kelas dobel di
 * jam+hari yang sama), lalu coba pindahkan SATU PER SATU ke slot lain yang
 * benar-benar kosong (kelas bebas, guru bebas, ruang bebas). Diulang beberapa
 * kali (max_pass) karena memindahkan satu gen bisa membuka slot untuk gen lain.
 */
function repair_konflik($kromosom, $guru_mapel, $semua_guru, $ruang_ids, $semua_slot_waktu, $max_pass = 15) {
    for ($pass = 0; $pass < $max_pass; $pass++) {

        // Bangun ulang peta pemakaian slot dari kondisi kromosom saat ini
        $used_guru  = [];
        $used_ruang = [];
        $used_kelas = [];
        foreach ($kromosom as $idx => $gen) {
            $kg = $gen['id_guru']  . '_' . $gen['hari'] . '_' . $gen['id_jam'];
            $kr = $gen['id_ruang'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
            $kk = $gen['id_kelas'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
            $used_guru[$kg][]  = $idx;
            $used_ruang[$kr][] = $idx;
            $used_kelas[$kk][] = $idx;
        }

        // Cari indeks gen yang jadi penyebab bentrok (yang "kedua dst" di slot yang sama)
        $idx_bentrok = [];
        foreach ($used_guru as $list)  if (count($list) > 1) foreach (array_slice($list, 1) as $i) $idx_bentrok[$i] = true;
        foreach ($used_ruang as $list) if (count($list) > 1) foreach (array_slice($list, 1) as $i) $idx_bentrok[$i] = true;
        foreach ($used_kelas as $list) if (count($list) > 1) foreach (array_slice($list, 1) as $i) $idx_bentrok[$i] = true;

        if (empty($idx_bentrok)) break; // sudah bersih total

        $ada_perbaikan = false;

        foreach (array_keys($idx_bentrok) as $idx) {
            $gen = $kromosom[$idx];

            $kg_old = $gen['id_guru']  . '_' . $gen['hari'] . '_' . $gen['id_jam'];
            $kr_old = $gen['id_ruang'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];
            $kk_old = $gen['id_kelas'] . '_' . $gen['hari'] . '_' . $gen['id_jam'];

            // Lepas dulu slot lama gen ini dari peta pemakaian
            $used_guru[$kg_old]  = array_diff($used_guru[$kg_old] ?? [], [$idx]);
            $used_ruang[$kr_old] = array_diff($used_ruang[$kr_old] ?? [], [$idx]);
            $used_kelas[$kk_old] = array_diff($used_kelas[$kk_old] ?? [], [$idx]);

            $slot_acak = $semua_slot_waktu;
            shuffle($slot_acak);

            $guru_utama = get_guru_for_mapel($gen['id_pelajaran'], $guru_mapel, $semua_guru);
            $found = false;

            foreach ([$guru_utama, $semua_guru] as $daftar_guru) {
                if ($found) break;
                foreach ($slot_acak as $slot) {
                    $hari_baru = $slot['hari'];
                    $jam_baru  = $slot['id_jam'];

                    $kk_new = $gen['id_kelas'] . '_' . $hari_baru . '_' . $jam_baru;
                    if (!empty($used_kelas[$kk_new])) continue;

                    $guru_list = $daftar_guru;
                    shuffle($guru_list);
                    $id_guru_ok = null;
                    foreach ($guru_list as $ig) {
                        $kg_new = $ig . '_' . $hari_baru . '_' . $jam_baru;
                        if (empty($used_guru[$kg_new])) { $id_guru_ok = $ig; break; }
                    }
                    if ($id_guru_ok === null) continue;

                    $ruang_acak = $ruang_ids;
                    shuffle($ruang_acak);
                    $id_ruang_ok = null;
                    foreach ($ruang_acak as $ir) {
                        $kr_new = $ir . '_' . $hari_baru . '_' . $jam_baru;
                        if (empty($used_ruang[$kr_new])) { $id_ruang_ok = $ir; break; }
                    }
                    if ($id_ruang_ok === null) continue;

                    // Ketemu slot bebas total -> pindahkan gen ini
                    $kromosom[$idx]['hari']    = $hari_baru;
                    $kromosom[$idx]['id_jam']  = $jam_baru;
                    $kromosom[$idx]['id_guru'] = $id_guru_ok;
                    $kromosom[$idx]['id_ruang']= $id_ruang_ok;

                    $used_guru[$id_guru_ok . '_' . $hari_baru . '_' . $jam_baru][]  = $idx;
                    $used_ruang[$id_ruang_ok . '_' . $hari_baru . '_' . $jam_baru][] = $idx;
                    $used_kelas[$kk_new][] = $idx;

                    $found = true;
                    $ada_perbaikan = true;
                    break;
                }
            }

            if (!$found) {
                // Belum ketemu slot bebas untuk gen ini pada pass ini,
                // kembalikan ke posisi semula supaya tidak "hilang" dari peta
                $used_guru[$kg_old][]  = $idx;
                $used_ruang[$kr_old][] = $idx;
                $used_kelas[$kk_old][] = $idx;
            }
        }

        if (!$ada_perbaikan) break; // tidak ada progres lagi, berhenti (kapasitas mentok)
    }

    return $kromosom;
}

// =============================================
// 4. INISIALISASI POPULASI (SMART)
// =============================================
$populasi = [];
$gagal_kapasitas = 0;
for ($i = 0; $i < $pop_size; $i++) {
    $populasi[] = buat_kromosom_smart(
        $kelas_ids, $kelas_pelajaran, $guru_mapel, $semua_guru,
        $semua_slot_waktu, $ruang_ids, $gagal_kapasitas
    );
}

// =============================================
// 5. EVOLUSI
// =============================================
$best_kromosom = null;
$best_fitness  = 0;
$best_generasi = 0;

for ($gen = 1; $gen <= $max_gen; $gen++) {

    $fitness_list = array_map('hitung_fitness', $populasi);

    $max_f = max($fitness_list);
    if ($max_f > $best_fitness) {
        $best_fitness  = $max_f;
        $best_generasi = $gen;
        $best_kromosom = $populasi[array_search($max_f, $fitness_list)];
    }

    if ($best_fitness >= 1.0) break;

    $populasi_baru = [$best_kromosom];

    while (count($populasi_baru) < $pop_size) {
        $p1   = seleksi_roulette($populasi, $fitness_list);
        $p2   = seleksi_roulette($populasi, $fitness_list);
        $anak = crossover($p1, $p2, $cross_rate);
        $anak = mutasi_smart($anak, $mut_rate, $guru_mapel, $semua_guru, $ruang_ids, $semua_slot_waktu);
        $populasi_baru[] = $anak;
    }

    $populasi = $populasi_baru;
}

// =============================================
// 6. [BARU] REPAIR PASCA-EVOLUSI
// Bersihkan sisa bentrok pada kromosom terbaik sebelum disimpan.
// =============================================
$bentrok_sebelum_repair = hitung_bentrok($best_kromosom);

if ($bentrok_sebelum_repair > 0) {
    $best_kromosom = repair_konflik($best_kromosom, $guru_mapel, $semua_guru, $ruang_ids, $semua_slot_waktu);
    $best_fitness  = hitung_fitness($best_kromosom);
}

// =============================================
// 7. HITUNG BENTROK FINAL (AKURAT)
// =============================================
$bentrok_final = hitung_bentrok($best_kromosom);

// =============================================
// 8. SIMPAN KE DATABASE
// =============================================
mysqli_query($conn, "DELETE FROM jadwal WHERE id_semester=$id_semester");

$saved = 0;
foreach ($best_kromosom as $gen) {
    $id_kelas     = (int)$gen['id_kelas'];
    $id_pelajaran = (int)$gen['id_pelajaran'];
    $id_guru      = (int)$gen['id_guru'];
    $id_jam       = (int)$gen['id_jam'];
    $id_ruang     = (int)$gen['id_ruang'];
    $hari         = mysqli_real_escape_string($conn, $gen['hari']);

    $sql = "INSERT INTO jadwal (id_kelas, id_pelajaran, id_guru, id_jam, id_ruang, hari, id_semester)
            VALUES ($id_kelas, $id_pelajaran, $id_guru, $id_jam, $id_ruang, '$hari', $id_semester)";
    if (mysqli_query($conn, $sql)) {
        $saved++;
    } else {
        error_log("GAGAL INSERT jadwal: " . mysqli_error($conn));
    }
}

mysqli_query($conn, "DELETE FROM hasil_ga WHERE id_semester=$id_semester");
mysqli_query($conn, "INSERT INTO hasil_ga (id_semester, generasi, nilai_fitness, status)
    VALUES ($id_semester, $best_generasi, $best_fitness, 'aktif')");

// =============================================
// 9. RETURN HASIL
// =============================================
$pesan = "Jadwal berhasil dibuat! $saved slot tersimpan. Bentrok: $bentrok_final";
if ($bentrok_final > 0) {
    $pesan .= ". Masih ada $bentrok_final bentrok yang tidak bisa diselesaikan otomatis"
            . " — kemungkinan kapasitas slot/guru/ruang tidak cukup untuk menampung"
            . " semua jam_per_minggu yang diminta. Coba cek jumlah jam pelajaran per"
            . " minggu di menu Kurikulum, atau tambah jumlah guru/ruang.";
}

echo json_encode([
    'success'  => true,
    'generasi' => $best_generasi,
    'fitness'  => $best_fitness,
    'bentrok'  => $bentrok_final,
    'saved'    => $saved,
    'message'  => $pesan
]);
?>
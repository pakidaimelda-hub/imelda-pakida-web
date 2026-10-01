HALO INI TES
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: jadwal_guru.php');
    exit;
}
?>
<?php include 'koneksi.php'; ?>
<?php
$hari_list = ['Senin','Selasa','Rabu','Kamis','Jumat'];

// Ambil jam unik saja (1 baris per Jam Ke-, tidak dobel per hari)
// [DIPERBAIKI] ORDER BY jam di-cast ke angka (CAST ... AS UNSIGNED) supaya urutannya
// 1,2,3,...,10,11 dan bukan urut abjad (1,10,11,2,3,...) seperti sebelumnya.
$res = mysqli_query($conn, "
    SELECT jam, MIN(mulai) as mulai, MIN(akhir) as akhir, COUNT(*) as jumlah_hari
    FROM jam
    WHERE hari != 'Sabtu'
    GROUP BY jam, mulai, akhir
    ORDER BY CAST(jam AS UNSIGNED) ASC, mulai ASC
");

$jam_rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $jam_rows[] = $r;
}

$total = count($jam_rows);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jam Pelajaran - SMAN 2 Toraja Utara</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --sidebar-bg: #1a3c5e;
      --sidebar-width: 240px;
      --accent: #4fc3f7;
    }
    body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }

    /* SIDEBAR */
    .sidebar { width: var(--sidebar-width); background: var(--sidebar-bg); min-height: 100vh;
               position: fixed; top: 0; left: 0; z-index: 100; overflow-y: auto; }
    .sidebar-brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-brand h6 { color: #fff; font-size: 14px; font-weight: 600; margin: 0; }
    .sidebar-brand small { color: rgba(255,255,255,0.5); font-size: 11px; }
    .nav-label { padding: 16px 20px 4px; font-size: 10px; color: rgba(255,255,255,0.35);
                 letter-spacing: 1px; text-transform: uppercase; }
    .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; font-size: 13.5px;
                         display: flex; align-items: center; gap: 10px;
                         border-left: 3px solid transparent; transition: all 0.15s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: rgba(255,255,255,0.08); color: #fff; border-left-color: var(--accent); }

    /* MAIN */
    .main-content { margin-left: var(--sidebar-width); }
    .topbar { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb;
              display: flex; align-items: center; justify-content: space-between;
              position: sticky; top: 0; z-index: 50; }
    .topbar h5 { font-size: 16px; font-weight: 600; color: #1a3c5e; margin: 0; }

    /* CARD */
    .card-box { background:#fff; border-radius:12px; border:1px solid #e5e7eb; padding:22px 24px; }

    /* TABLE */
    table.jam-table { width:100%; border-collapse: collapse; }
    table.jam-table thead th {
      text-align:left; font-size:13px; color:#1a3c5e; font-weight:700;
      padding: 10px 8px; border-bottom: 2px solid #e5e7eb;
    }
    table.jam-table tbody td {
      padding: 12px 8px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size:14px;
    }
    table.jam-table tbody tr:hover { background:#f8fafc; }

    .badge-jamke {
      width:34px; height:34px; border-radius:50%; background:#dbeafe; color:#1e40af;
      display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:14px;
    }
    .pill-mulai {
      display:inline-flex; align-items:center; gap:6px; border:1.5px solid #38bdf8;
      color:#0284c7; background:#f0f9ff; border-radius:20px; padding:5px 14px; font-size:13px; font-weight:600;
    }
    .pill-selesai {
      display:inline-flex; align-items:center; gap:6px; background:#f97316;
      color:#fff; border-radius:20px; padding:5px 14px; font-size:13px; font-weight:600;
    }
    .pill-durasi {
      display:inline-block; background:#f1f5f9; color:#334155; border:1px solid #e2e8f0;
      border-radius:8px; padding:5px 12px; font-size:13px; font-weight:600;
    }
    .btn-icon {
      width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center;
      justify-content:center; border:1.5px solid; background:#fff;
    }
    .btn-icon-edit { border-color:#3b82f6; color:#3b82f6; }
    .btn-icon-edit:hover { background:#3b82f6; color:#fff; }
    .btn-icon-del { border-color:#ef4444; color:#ef4444; }
    .btn-icon-del:hover { background:#ef4444; color:#fff; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-brand">
    <h6><i class="bi bi-calendar3 me-2"></i>Sistem Jadwal</h6>
    <small>SMAN 2 Toraja Utara</small>
  </div>
  <div class="nav-label">Menu Utama</div>
  <a href="index.php"    class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
  <a href="semester.php" class="nav-link"><i class="bi bi-calendar-range"></i> Semester</a>
  <div class="nav-label">Data Master</div>
  <a href="guru.php"     class="nav-link"><i class="bi bi-person-badge"></i> Guru</a>
  <a href="mk.php"       class="nav-link"><i class="bi bi-book"></i> Mata Pelajaran</a>
  <a href="jam.php"      class="nav-link active"><i class="bi bi-clock"></i> Jam Pelajaran</a>
  <a href="kelas.php"    class="nav-link"><i class="bi bi-mortarboard"></i> Kelas</a>
  <a href="ruang.php"    class="nav-link"><i class="bi bi-door-open"></i> Ruang Kelas</a>
  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php"   class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
  <a href="arsip.php"    class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-clock me-2"></i>Jam Pelajaran</h5>
    <div class="d-flex align-items-center gap-3">
      <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
    </div>
  </div>

  <div class="p-4">

    <?php if(isset($_GET['status'])): ?>
      <?php
        $alert_map = [
          'tambah' => ['success','check-circle','Jam pelajaran berhasil ditambahkan!'],
          'edit'   => ['info',   'pencil',      'Jam pelajaran berhasil diubah!'],
          'hapus'  => ['warning','trash',       'Jam pelajaran berhasil dihapus!'],
          'duplikat' => ['danger','exclamation-triangle', 'Jam ke- tersebut sudah ada, gunakan nomor lain!'],
        ];
        $s = $alert_map[$_GET['status']] ?? null;
        if($s):
      ?>
      <div class="alert alert-<?= $s[0] ?> alert-dismissible fade show">
        <i class="bi bi-<?= $s[1] ?> me-2"></i><?= $s[2] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; endif; ?>

    <div class="row g-4">
      <!-- TABEL -->
      <div class="col-lg-12">
        <div class="card-box">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <h6 class="fw-bold mb-1" style="color:#1a3c5e;font-size:16px;">Daftar Jam Pelajaran</h6>
              <small class="text-muted">Total: <?= $total ?> slot jam</small>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i>Tambah Jam
            </button>
          </div>

          <?php if(empty($jam_rows)): ?>
            <div class="text-center py-5 text-muted">
              <i class="bi bi-clock" style="font-size:56px;opacity:0.2;"></i>
              <p class="mt-3 mb-2">Belum ada data jam pelajaran.</p>
            </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="jam-table">
              <thead>
                <tr>
                  <th style="width:40px;">No</th>
                  <th>Jam Ke-</th>
                  <th>Mulai</th>
                  <th>Selesai</th>
                  <th>Durasi</th>
                  <th style="width:100px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach($jam_rows as $j):
                  $durasi = round((strtotime($j['akhir']) - strtotime($j['mulai'])) / 60);
                ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><span class="badge-jamke"><?= $j['jam'] ?></span></td>
                  <td><span class="pill-mulai"><i class="bi bi-play-fill"></i><?= substr($j['mulai'],0,5) ?></span></td>
                  <td><span class="pill-selesai"><i class="bi bi-stop-fill"></i><?= substr($j['akhir'],0,5) ?></span></td>
                  <td><span class="pill-durasi"><?= $durasi ?> menit</span></td>
                  <td>
                    <button class="btn-icon btn-icon-edit"
                            data-bs-toggle="modal" data-bs-target="#modalEdit"
                            data-jam="<?= $j['jam'] ?>"
                            data-mulai="<?= $j['mulai'] ?>"
                            data-akhir="<?= $j['akhir'] ?>"
                            title="Edit">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="hapus_jam.php?jam=<?= $j['jam'] ?>"
                       class="btn-icon btn-icon-del"
                       onclick="return confirm('Hapus jam ke-<?= $j['jam'] ?> untuk semua hari?')"
                       title="Hapus">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white"><i class="bi bi-plus-circle me-2"></i>Tambah Jam Pelajaran</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_tambah_jam.php" method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Jam Ke- <span class="text-danger">*</span></label>
            <input type="number" name="jam" class="form-control form-control-sm"
                   placeholder="Contoh: 1, 2, 3..." min="1" required>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Mulai <span class="text-danger">*</span></label>
              <input type="time" name="mulai" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Selesai <span class="text-danger">*</span></label>
              <input type="time" name="akhir" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="mt-2" style="font-size:12px;color:#64748b;">
            <i class="bi bi-info-circle me-1"></i>Otomatis berlaku untuk Senin&ndash;Jumat.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header" style="background:#1a3c5e;">
        <h6 class="modal-title text-white"><i class="bi bi-pencil me-2"></i>Edit Jam Pelajaran</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="proses_edit_jam.php" method="POST">
        <input type="hidden" name="jam_lama" id="editJamLama">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Jam Ke- <span class="text-danger">*</span></label>
            <input type="number" name="jam" id="editJam" class="form-control form-control-sm" min="1" required>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Mulai <span class="text-danger">*</span></label>
              <input type="time" name="mulai" id="editMulai" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold" style="font-size:13px;">Selesai <span class="text-danger">*</span></label>
              <input type="time" name="akhir" id="editAkhir" class="form-control form-control-sm" required>
            </div>
          </div>
          <div class="mt-2" style="font-size:12px;color:#64748b;">
            <i class="bi bi-info-circle me-1"></i>Perubahan berlaku untuk semua hari.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEdit').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('editJamLama').value = btn.dataset.jam;
  document.getElementById('editJam').value     = btn.dataset.jam;
  document.getElementById('editMulai').value   = btn.dataset.mulai.substring(0,5);
  document.getElementById('editAkhir').value   = btn.dataset.akhir.substring(0,5);
});
</script>
</body>
</html>
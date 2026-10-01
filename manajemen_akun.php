<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Proses tambah akun
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama     = trim($_POST['nama']);
    $id_guru  = (int)$_POST['id_guru'];

    // [DIPERBAIKI] Pakai prepared statement supaya nama/username yang
    // mengandung tanda kutip (') tidak merusak query SQL.
    $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt_cek, 's', $username);
    mysqli_stmt_execute($stmt_cek);
    $hasil_cek = mysqli_stmt_get_result($stmt_cek);

    if (mysqli_num_rows($hasil_cek) > 0) {
        $pesan = '<div class="alert alert-danger">Username sudah dipakai!</div>';
    } else {
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, password, nama, role, id_guru)
                                               VALUES (?, ?, ?, 'guru', ?)");
        mysqli_stmt_bind_param($stmt_insert, 'sssi', $username, $password, $nama, $id_guru);

        if (mysqli_stmt_execute($stmt_insert)) {
            $pesan = '<div class="alert alert-success">Akun guru berhasil ditambahkan!</div>';
        } else {
            $pesan = '<div class="alert alert-danger">Gagal menambahkan akun: ' . htmlspecialchars(mysqli_stmt_error($stmt_insert)) . '</div>';
        }
        mysqli_stmt_close($stmt_insert);
    }
    mysqli_stmt_close($stmt_cek);
}

// Proses edit akun (username & password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id_edit      = (int)$_POST['id_edit'];
    $username_baru = trim($_POST['username_edit']);
    $password_baru = trim($_POST['password_edit']);

    if ($username_baru === '') {
        $pesan = '<div class="alert alert-danger">Username tidak boleh kosong!</div>';
    } else {
        // Cek apakah username sudah dipakai user lain
        $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
        mysqli_stmt_bind_param($stmt_cek, 'si', $username_baru, $id_edit);
        mysqli_stmt_execute($stmt_cek);
        $hasil_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $pesan = '<div class="alert alert-danger">Username sudah dipakai akun lain!</div>';
        } else {
            if ($password_baru !== '') {
                // Update username + password
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username = ?, password = ? WHERE id = ? AND role = 'guru'");
                mysqli_stmt_bind_param($stmt_update, 'ssi', $username_baru, $password_hash, $id_edit);
            } else {
                // Update username saja, password tetap
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ? AND role = 'guru'");
                mysqli_stmt_bind_param($stmt_update, 'si', $username_baru, $id_edit);
            }

            if (mysqli_stmt_execute($stmt_update)) {
                $pesan = '<div class="alert alert-success">Akun guru berhasil diperbarui!</div>';
            } else {
                $pesan = '<div class="alert alert-danger">Gagal memperbarui akun: ' . htmlspecialchars(mysqli_stmt_error($stmt_update)) . '</div>';
            }
            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt_cek);
    }
}

// Proses edit akun ADMIN (akun yang sedang login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin'])) {
    $id_admin      = (int)$_SESSION['user_id'];
    $username_baru = trim($_POST['username_admin']);
    $password_baru = trim($_POST['password_admin']);

    if ($username_baru === '') {
        $pesan = '<div class="alert alert-danger">Username tidak boleh kosong!</div>';
    } else {
        $stmt_cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
        mysqli_stmt_bind_param($stmt_cek, 'si', $username_baru, $id_admin);
        mysqli_stmt_execute($stmt_cek);
        $hasil_cek = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $pesan = '<div class="alert alert-danger">Username sudah dipakai akun lain!</div>';
        } elseif ($password_baru !== '' && strlen($password_baru) < 6) {
            $pesan = '<div class="alert alert-danger">Password baru minimal 6 karakter!</div>';
        } else {
            if ($password_baru !== '') {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username = ?, password = ? WHERE id = ? AND role = 'admin'");
                mysqli_stmt_bind_param($stmt_update, 'ssi', $username_baru, $password_hash, $id_admin);
            } else {
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username = ? WHERE id = ? AND role = 'admin'");
                mysqli_stmt_bind_param($stmt_update, 'si', $username_baru, $id_admin);
            }
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            $_SESSION['username'] = $username_baru;
            $pesan = '<div class="alert alert-success">Akun admin berhasil diperbarui!</div>';
        }
        mysqli_stmt_close($stmt_cek);
    }
}

// Proses hapus akun
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id AND role='guru'");
    header('Location: manajemen_akun.php?berhasil=hapus');
    exit;
}

if (isset($_GET['berhasil'])) {
    $pesan = '<div class="alert alert-success">Akun berhasil dihapus!</div>';
}

// Ambil data username admin saat ini
$stmt_admin = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ? AND role = 'admin'");
mysqli_stmt_bind_param($stmt_admin, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt_admin);
$admin_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_admin));
mysqli_stmt_close($stmt_admin);

// Ambil data
$users = mysqli_query($conn, "SELECT u.*, g.nama as nama_guru FROM users u LEFT JOIN guru g ON u.id_guru = g.id_guru WHERE u.role='guru' ORDER BY u.id ASC");
$daftar_guru = mysqli_query($conn, "SELECT * FROM guru ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Akun Guru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --sidebar-bg: #1a3c5e; --sidebar-width: 240px; --accent: #4fc3f7; }
    body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
    .sidebar { width: var(--sidebar-width); background: var(--sidebar-bg); min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 100; }
    .sidebar-brand { padding: 20px 16px; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidebar-brand h6 { color: #fff; font-size: 14px; font-weight: 600; margin: 0; }
    .sidebar-brand small { color: rgba(255,255,255,0.5); font-size: 11px; }
    .nav-label { padding: 16px 20px 4px; font-size: 10px; color: rgba(255,255,255,0.35); letter-spacing: 1px; text-transform: uppercase; }
    .sidebar .nav-link { color: rgba(255,255,255,0.7); padding: 10px 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; border-left: 3px solid transparent; transition: all 0.15s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.08); color: #fff; border-left-color: var(--accent); }
    .main-content { margin-left: var(--sidebar-width); }
    .topbar { background: #fff; padding: 14px 24px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
    .topbar h5 { font-size: 16px; font-weight: 600; color: #1a3c5e; margin: 0; }
    .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

    /* Tabel Daftar Akun Guru */
    .tabel-akun { font-size: 13.5px; }
    .tabel-akun thead th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.4px; white-space: nowrap; vertical-align: middle; }
    .tabel-akun td { vertical-align: middle; }
    .tabel-akun td.col-no { width: 40px; text-align: center; color: #6b7280; }
    .tabel-akun td.col-username { white-space: nowrap; }
    .tabel-akun td.col-username code { background: #eef2ff; color: #3730a3; padding: 4px 8px; border-radius: 6px; font-size: 12.5px; }
    .tabel-akun td.col-aksi { width: 190px; }
    .aksi-group { display: flex; flex-wrap: nowrap; gap: 6px; }
    .aksi-group .btn { white-space: nowrap; font-size: 12.5px; padding: 5px 10px; }
    .tabel-akun tbody tr:hover { background-color: #f8fafc; }

    /* Ubah Akun Admin - kolom disamakan tingginya (label + input/tombol + keterangan) */
    .admin-form-col { display: flex; flex-direction: column; }
    .admin-form-col .form-label { min-height: 18px; }
    .admin-form-col .form-control,
    .admin-form-col .btn { height: 38px; }
    .admin-form-col small { min-height: 18px; display: block; margin-top: 4px; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <h6><i class="bi bi-calendar3 me-2"></i>Sistem Jadwal</h6>
    <small>SMAN 2 Toraja Utara</small>
  </div>
  <div class="nav-label">Menu Utama</div>
  <a href="index.php" class="nav-link"><i class="bi bi-grid-1x2"></i> Dashboard</a>
  <a href="semester.php" class="nav-link"><i class="bi bi-calendar-range"></i> Semester</a>
  <div class="nav-label">Data Master</div>
  <a href="guru.php" class="nav-link"><i class="bi bi-person-badge"></i> Guru</a>
  <a href="mk.php" class="nav-link"><i class="bi bi-book"></i> Mata Pelajaran</a>
  <a href="jam.php" class="nav-link"><i class="bi bi-clock"></i> Jam Pelajaran</a>
  <a href="kelas.php" class="nav-link"><i class="bi bi-mortarboard"></i> Kelas</a>
  <a href="ruang.php" class="nav-link"><i class="bi bi-door-open"></i> Ruang Kelas</a>
<a href="kurikulum.php" class="nav-link"><i class="bi bi-journal-bookmark"></i> Kurikulum</a>

  <div class="nav-label">Akun</div>
  <a href="manajemen_akun.php" class="nav-link active"><i class="bi bi-people"></i> Manajemen Akun</a>
  <div class="nav-label">Penjadwalan</div>
  <a href="generate.php" class="nav-link"><i class="bi bi-gear-wide-connected"></i> Generate Jadwal</a>
  <a href="jadwal.php" class="nav-link"><i class="bi bi-table"></i> Lihat Jadwal</a>
        <a href="arsip.php" class="nav-link"><i class="bi bi-archive"></i> Arsip Jadwal</a>
  <div class="nav-label">Keluar</div>
  <a href="login.php?logout=1" class="nav-link"><i class="bi bi-box-arrow-left"></i> Logout</a>
</div>

<div class="main-content">
  <div class="topbar">
    <h5><i class="bi bi-people me-2"></i>Manajemen Akun Guru</h5>
    <a href="login.php?logout=1" class="admin-link text-muted text-decoration-none" style="font-size:13px;" title="Klik untuk logout" onclick="return confirm('Yakin ingin logout?');"><i class="bi bi-person-circle me-1"></i>Admin</a>
  </div>

  <div class="p-4">
    <?= $pesan ?>

    <div class="card p-4 mb-4">
      <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Ubah Akun Admin</h6>
      <form method="POST" class="row g-3">
        <div class="col-md-4 admin-form-col">
          <label class="form-label fw-semibold" style="font-size:12px;">Username</label>
          <input type="text" name="username_admin" class="form-control" value="<?= htmlspecialchars($admin_data['username'] ?? '') ?>" required>
          <small>&nbsp;</small>
        </div>
        <div class="col-md-4 admin-form-col">
          <label class="form-label fw-semibold" style="font-size:12px;">Password Baru</label>
          <input type="password" name="password_admin" class="form-control" placeholder="Kosongkan jika tidak diubah">
          <small class="text-muted">Min. 6 karakter jika diisi.</small>
        </div>
        <div class="col-md-4 admin-form-col">
          <label class="form-label fw-semibold" style="font-size:12px;">&nbsp;</label>
          <button type="submit" name="edit_admin" class="btn btn-outline-primary w-100"
                  onclick="return confirm('Yakin ingin mengubah akun admin?');">
            <i class="bi bi-save me-1"></i>Simpan Perubahan
          </button>
          <small>&nbsp;</small>
        </div>
      </form>
    </div>

    <div class="row g-4">
      <!-- Form Tambah -->
      <div class="col-lg-4 col-md-5">
        <div class="card p-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-person-plus me-2"></i>Tambah Akun Guru</h6>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Pilih Guru</label>
              <select name="id_guru" class="form-select" required onchange="isiNama(this)">
                <option value="">-- Pilih Guru --</option>
                <?php while($g = mysqli_fetch_assoc($daftar_guru)): ?>
                <option value="<?= $g['id_guru'] ?>" data-nama="<?= htmlspecialchars($g['nama']) ?>"><?= htmlspecialchars($g['nama']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Nama Lengkap</label>
              <input type="text" name="nama" id="inputNama" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Username</label>
              <input type="text" name="username" class="form-control" placeholder="cth: billgates" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold" style="font-size:12px;">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
            </div>
            <button type="submit" name="tambah" class="btn btn-primary w-100">
              <i class="bi bi-plus-circle me-1"></i>Tambah Akun
            </button>
          </form>
        </div>
      </div>

      <!-- Tabel Daftar Akun -->
      <div class="col-lg-8 col-md-7">
        <div class="card p-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-list-ul me-2"></i>Daftar Akun Guru</h6>
          <div class="table-responsive">
          <table class="table table-hover align-middle tabel-akun mb-0">
            <thead class="table-dark">
              <tr>
                <th class="col-no">No</th>
                <th>Username</th>
                <th>Nama Guru</th>
                <th class="col-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody>
            <?php $no = 1; while($u = mysqli_fetch_assoc($users)): ?>
              <tr>
                <td class="col-no"><?= $no++ ?></td>
                <td class="col-username"><code><?= htmlspecialchars($u['username']) ?></code></td>
                <td><?= htmlspecialchars($u['nama_guru'] ?? '-') ?></td>
                <td class="col-aksi">
                  <div class="aksi-group">
                    <button type="button" class="btn btn-warning btn-sm text-white btn-edit-akun"
                            data-id="<?= (int)$u['id'] ?>"
                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
                            data-nama="<?= htmlspecialchars($u['nama'], ENT_QUOTES) ?>">
                      <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <a href="manajemen_akun.php?hapus=<?= $u['id'] ?>"
                       class="btn btn-danger btn-sm btn-hapus-akun"
                       data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>">
                      <i class="bi bi-trash"></i> Hapus
                    </a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit Akun -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Akun Guru</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_edit" id="editId">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:12px;">Nama Guru</label>
            <input type="text" id="editNamaTampil" class="form-control" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:12px;">Username</label>
            <input type="text" name="username_edit" id="editUsername" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:12px;">Password Baru</label>
            <input type="password" name="password_edit" id="editPassword" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function isiNama(sel) {
  const nama = sel.options[sel.selectedIndex].dataset.nama;
  document.getElementById('inputNama').value = nama || '';
}

function bukaModalEdit(id, username, nama) {
  document.getElementById('editId').value = id;
  document.getElementById('editUsername').value = username;
  document.getElementById('editNamaTampil').value = nama;
  document.getElementById('editPassword').value = '';
  const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
  modal.show();
}

// Pasang event listener ke semua tombol Edit (aman untuk nama yang mengandung tanda kutip)
document.querySelectorAll('.btn-edit-akun').forEach(function (btn) {
  btn.addEventListener('click', function () {
    bukaModalEdit(this.dataset.id, this.dataset.username, this.dataset.nama);
  });
});

// Konfirmasi hapus (juga via data-attribute, aman dari tanda kutip)
document.querySelectorAll('.btn-hapus-akun').forEach(function (btn) {
  btn.addEventListener('click', function (e) {
    if (!confirm('Hapus akun ' + this.dataset.username + '?')) {
      e.preventDefault();
    }
  });
});
</script>
</body>
</html>
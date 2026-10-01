<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

include 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $username_safe = mysqli_real_escape_string($conn, $username);
        $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_safe' LIMIT 1");

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['id_guru']  = $user['id_guru'];

                if ($user['role'] === 'admin') {
                    header('Location: index.php');
                    exit;
                } else {
                    header('Location: jadwal_guru.php');
                    exit;
                }

            } else {
                $error = 'Kata sandi salah.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    } else {
        $error = 'Harap isi username dan kata sandi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login &mdash; Sistem Penjadwalan SMAN 2 Toraja Utara</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    :root {
      --primary: #e8789a;
      --primary-dark: #d45f82;
      --primary-light: #fdeef3;
      --accent: #ffb3c6;
      --soft-pink: #f9c5d5;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #fdeef3 0%, #fff5f8 50%, #ffffff 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
      padding: 1.5rem;
    }

    .login-wrapper {
      width: 100%;
      max-width: 920px;
      display: flex;
      background: #fff;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 12px 50px rgba(232, 120, 154, 0.18);
      min-height: 580px;
    }

    /* ===== KIRI ===== */
    .login-left {
      flex: 1.1;
      padding: 3rem 2.5rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #fff;
    }

    .brand {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 2.5rem;
    }

    .brand-icon {
      width: 44px; height: 44px;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      color: white; font-size: 19px; flex-shrink: 0;
      box-shadow: 0 4px 12px rgba(232,120,154,0.35);
    }

    .brand-name {
      font-size: 15px; font-weight: 700;
      color: var(--primary); line-height: 1.2;
    }

    .brand-sub { font-size: 11px; color: #b0b0b0; }

    .login-heading {
      font-size: 27px; font-weight: 700;
      color: #2d1b2e; margin-bottom: 0.3rem; line-height: 1.25;
    }

    .login-subheading { font-size: 13px; color: #a0a0a0; margin-bottom: 2rem; }

    .form-label {
      font-size: 11.5px; font-weight: 700;
      color: #555; letter-spacing: 0.5px;
      text-transform: uppercase; margin-bottom: 6px;
    }

    .form-control {
      height: 46px; font-size: 14px;
      border: 1.5px solid #f5d0dc;
      border-radius: 12px; background: #fffafc;
      color: #111; transition: border-color 0.15s, box-shadow 0.15s;
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(232,120,154,0.12);
      background: #fff;
    }

    .input-group .form-control { border-right: none; border-radius: 12px 0 0 12px; }

    .btn-toggle-pw {
      border: 1.5px solid #f5d0dc; border-left: none;
      border-radius: 0 12px 12px 0;
      background: #fffafc; color: #d4a0b0;
      padding: 0 14px; cursor: pointer; transition: color 0.15s;
    }
    .btn-toggle-pw:hover { color: var(--primary); }

    .row-options {
      display: flex; align-items: center;
      justify-content: space-between;
      margin: 0.8rem 0 1.5rem;
    }

    .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

    .link-forgot { font-size: 12px; color: var(--primary); text-decoration: none; }
    .link-forgot:hover { text-decoration: underline; }

    .btn-login {
      height: 50px;
      background: linear-gradient(135deg, var(--primary) 0%, #f4a0bc 100%);
      color: #fff; border: none; border-radius: 12px;
      font-size: 14px; font-weight: 600; width: 100%;
      cursor: pointer; transition: opacity 0.15s, transform 0.1s;
      box-shadow: 0 6px 18px rgba(232,120,154,0.35);
      letter-spacing: 0.3px;
    }
    .btn-login:hover { opacity: 0.9; color: #fff; }
    .btn-login:active { transform: scale(0.99); }

    .divider {
      display: flex; align-items: center; gap: 10px; margin: 1.2rem 0;
    }
    .divider-line { flex: 1; height: 1px; background: #f5d0dc; }
    .divider-text { font-size: 11px; color: #c8a0b0; white-space: nowrap; }

    .btn-otp {
      height: 46px; background: #fffafc;
      border: 1.5px solid #f5d0dc; border-radius: 12px;
      font-size: 13px; color: #555; width: 100%;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      transition: all 0.15s; cursor: pointer;
    }
    .btn-otp:hover { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }

    .alert-error {
      background: #fee2e2; border: 1px solid #fca5a5;
      color: #991b1b; border-radius: 12px;
      padding: 10px 14px; font-size: 13px;
      margin-bottom: 1.25rem;
      display: flex; align-items: center; gap: 8px;
    }

    .login-footer {
      margin-top: 1.5rem; font-size: 12px;
      color: #b0b0b0; text-align: center;
    }
    .login-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }

    /* ===== KANAN ===== */
    .login-right {
      flex: 0.95;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }

    /* Foto sekolah sebagai background */
    .bg-school {
      position: absolute;
      inset: 0;
      background-image: url('sekolah.jpg');
      background-size: cover;
      background-position: center top;
      filter: brightness(0.75) saturate(0.9);
    }

    /* Overlay pink soft gradient di atas foto */
    .bg-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(
        to bottom,
        rgba(248, 180, 200, 0.35) 0%,
        rgba(232, 120, 154, 0.55) 40%,
        rgba(180, 60, 100, 0.85) 100%
      );
    }

    /* Konten di atas overlay */
    .right-content {
      position: relative;
      z-index: 2;
      padding: 2rem 2rem 2.5rem;
      display: flex;
      flex-direction: column;
      gap: 1.25rem;
    }

    .right-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(6px);
      color: #fff; font-size: 11px; font-weight: 600;
      padding: 5px 14px; border-radius: 20px;
      width: fit-content;
      border: 1px solid rgba(255,255,255,0.3);
    }

    .right-title {
      font-size: 22px; font-weight: 800;
      color: #fff; line-height: 1.35;
      text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
    .feature-list li {
      display: flex; align-items: center; gap: 10px;
      font-size: 13px; color: rgba(255,255,255,0.9);
    }
    .feature-list li i { color: #ffcfe0; font-size: 14px; flex-shrink: 0; }

    .stats-row {
      display: flex; gap: 1.5rem;
      padding-top: 1rem;
      border-top: 1px solid rgba(255,255,255,0.2);
    }
    .stat-num { font-size: 24px; font-weight: 800; color: #fff; line-height: 1; }
    .stat-label { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 2px; }

    /* OTP Modal */
    .otp-backdrop {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.4); z-index: 999;
      align-items: center; justify-content: center;
    }
    .otp-backdrop.show { display: flex; }
    .otp-modal {
      background: #fff; border-radius: 20px;
      padding: 2rem; width: 340px;
      box-shadow: 0 20px 60px rgba(232,120,154,0.25);
      text-align: center;
    }
    .otp-icon-wrap {
      width: 56px; height: 56px; border-radius: 50%;
      background: var(--primary-light);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1rem; font-size: 24px; color: var(--primary);
    }
    .otp-modal h5 { font-size: 18px; font-weight: 700; color: #2d1b2e; margin-bottom: 0.3rem; }
    .otp-modal p { font-size: 13px; color: #9ca3af; margin-bottom: 1.5rem; }
    .otp-inputs { display: flex; gap: 8px; justify-content: center; margin-bottom: 1.5rem; }
    .otp-inputs input {
      width: 44px; height: 52px; text-align: center;
      font-size: 20px; font-weight: 700;
      border: 1.5px solid #f5d0dc; border-radius: 10px;
      background: #fffafc; color: var(--primary); outline: none;
    }
    .otp-inputs input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232,120,154,0.12); }

    @media (max-width: 680px) {
      .login-right { display: none; }
      .login-left { padding: 2rem 1.5rem; }
    }
  </style>
</head>
<body>

<!-- OTP Modal -->
<div class="otp-backdrop" id="otpBackdrop">
  <div class="otp-modal">
    <div class="otp-icon-wrap"><i class="bi bi-phone"></i></div>
    <h5>Verifikasi OTP</h5>
    <p>Masukkan 6 digit kode OTP yang telah dikirim ke nomor HP terdaftar.</p>
    <div class="otp-inputs" id="otpInputs">
      <input type="text" maxlength="1" inputmode="numeric" />
      <input type="text" maxlength="1" inputmode="numeric" />
      <input type="text" maxlength="1" inputmode="numeric" />
      <input type="text" maxlength="1" inputmode="numeric" />
      <input type="text" maxlength="1" inputmode="numeric" />
      <input type="text" maxlength="1" inputmode="numeric" />
    </div>
    <button class="btn-login mb-2" onclick="verifyOtp()">
      <i class="bi bi-check-circle me-2"></i>Verifikasi
    </button>
    <button class="btn-otp mt-1" onclick="closeOtp()">Batal</button>
  </div>
</div>

<div class="login-wrapper">

  <!-- KIRI: Form -->
  <div class="login-left">
    <div class="brand">
      <div class="brand-icon"><i class="bi bi-calendar3"></i></div>
      <div>
        <div class="brand-name">SMAN 2 Toraja Utara</div>
        <div class="brand-sub">Sistem Penjadwalan Mata Pelajaran</div>
      </div>
    </div>

    <h1 class="login-heading">Selamat datang<br>kembali.</h1>
    <p class="login-subheading">Masuk untuk mengelola jadwal mata pelajaran.</p>

    <?php if ($error): ?>
    <div class="alert-error">
      <i class="bi bi-exclamation-circle-fill"></i>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="mb-3">
        <label class="form-label" for="username">NIP / Username</label>
        <input class="form-control" id="username" name="username" type="text"
               placeholder="Masukkan NIP atau username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               autocomplete="username" required />
      </div>
      <div class="mb-1">
        <label class="form-label" for="password">Kata Sandi</label>
        <div class="input-group">
          <input class="form-control" id="password" name="password"
                 type="password" placeholder="••••••••"
                 autocomplete="current-password" required />
          <button type="button" class="btn-toggle-pw" onclick="togglePassword()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>
      <div class="row-options">
        <div class="form-check mb-0">
          <input class="form-check-input" type="checkbox" id="rememberMe" name="remember" />
          <label class="form-check-label" for="rememberMe" style="font-size:13px;color:#b0b0b0;">Ingat saya</label>
        </div>
        <a href="lupa_password.php" class="link-forgot">Lupa kata sandi?</a>
      </div>
      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Sistem
      </button>
    </form>

    <div class="divider">
      <div class="divider-line"></div>
      <span class="divider-text">atau gunakan verifikasi 2 langkah</span>
      <div class="divider-line"></div>
    </div>

    <button class="btn-otp" type="button" onclick="openOtp()">
      <i class="bi bi-phone" style="color:var(--primary);"></i>
      Masuk dengan kode OTP
    </button>

    <p class="login-footer">Butuh akses? Hubungi <a href="mailto:admin@sman2torut.sch.id">Admin Sekolah</a></p>
  </div>

  <!-- KANAN: Foto Sekolah -->
  <div class="login-right">
    <div class="bg-school"></div>
    <div class="bg-overlay"></div>

    <div class="right-content">
      <div class="right-badge">
        <i class="bi bi-circle-fill" style="font-size:7px;"></i>
        Tahun Ajaran 2024/2025
      </div>
      <div class="right-title">Kelola jadwal dengan<br>mudah &amp; efisien</div>
      <ul class="feature-list">
        <li><i class="bi bi-check-circle-fill"></i> Atur jadwal kelas &amp; mata pelajaran otomatis</li>
        <li><i class="bi bi-check-circle-fill"></i> Kelola data guru dan ruang kelas</li>
        <li><i class="bi bi-check-circle-fill"></i> Deteksi konflik jadwal secara real-time</li>
        <li><i class="bi bi-check-circle-fill"></i> Generate jadwal dengan Algoritma Genetika</li>
        <li><i class="bi bi-check-circle-fill"></i> Ekspor jadwal ke PDF &amp; Excel</li>
      </ul>
      <div class="stats-row">
        <div>
          <div class="stat-num">36+</div>
          <div class="stat-label">Kelas aktif</div>
        </div>
        <div>
          <div class="stat-num">80+</div>
          <div class="stat-label">Guru terdaftar</div>
        </div>
        <div>
          <div class="stat-num">100%</div>
          <div class="stat-label">Jadwal tersusun</div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    pw.type = pw.type === 'password' ? 'text' : 'password';
    icon.className = pw.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
  }
  function openOtp() {
    document.getElementById('otpBackdrop').classList.add('show');
    document.querySelectorAll('#otpInputs input')[0].focus();
  }
  function closeOtp() {
    document.getElementById('otpBackdrop').classList.remove('show');
    document.querySelectorAll('#otpInputs input').forEach(i => i.value = '');
  }
  function verifyOtp() {
    const digits = [...document.querySelectorAll('#otpInputs input')].map(i => i.value).join('');
    if (digits.length < 6) { alert('Masukkan 6 digit kode OTP.'); return; }
    alert('OTP: ' + digits + '\n(Sambungkan ke backend)');
    closeOtp();
  }
  document.querySelectorAll('#otpInputs input').forEach((input, idx, inputs) => {
    input.addEventListener('input', () => {
      input.value = input.value.replace(/[^0-9]/g, '');
      if (input.value && idx < inputs.length - 1) inputs[idx + 1].focus();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && idx > 0) inputs[idx - 1].focus();
    });
  });
  document.getElementById('otpBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeOtp();
  });
</script>
</body>
</html>
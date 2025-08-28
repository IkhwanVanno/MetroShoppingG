<main class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <form action="$BaseHref/auth/reset-password?token=$Token" method="POST" class="w-100 p-4 border rounded shadow-sm" style="max-width: 400px;">
    <h4 class="mb-3">Reset Sandi</h4>
    <p class="text-muted mb-4">Masukkan password baru untuk akun Anda.</p>
    
    <div class="mb-3">
      <label for="new_password_1" class="form-label">Password Baru</label>
      <input type="password" class="form-control" id="new_password_1" name="new_password_1" required minlength="6">
    </div>

    <div class="mb-3">
      <label for="new_password_2" class="form-label">Konfirmasi Password Baru</label>
      <input type="password" class="form-control" id="new_password_2" name="new_password_2" required minlength="6">
    </div>

    <button type="submit" class="btn btn-success w-100 mb-3">Reset Password</button>
    
    <div class="text-center">
      <p class="mb-0">Kembali ke <a href="$BaseHref/auth/login">halaman login</a></p>
    </div>
  </form>
</main>
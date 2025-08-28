<main class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <form action="$BaseHref/auth/forgot-password" method="POST" class="w-100 p-4 border rounded shadow-sm" style="max-width: 400px;">
    <h4 class="mb-3">Lupa Sandi</h4>
    <p class="text-muted mb-4">Masukkan email Anda untuk menerima link reset password.</p>
    
    <div class="mb-3">
      <label for="forgot_email" class="form-label">Email</label>
      <input type="email" class="form-control" id="forgot_email" name="forgot_email" required>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">Kirim Link Reset</button>
    
    <div class="text-center">
      <p class="mb-2">Ingat password Anda? <a href="$BaseHref/auth/login">Masuk di sini</a></p>
      <p class="mb-0">Belum punya akun? <a href="$BaseHref/auth/register">Daftar di sini</a></p>
    </div>
  </form>
</main>
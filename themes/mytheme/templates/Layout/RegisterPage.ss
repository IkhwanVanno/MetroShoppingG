<main class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <form action="$BaseHref/auth/register" method="POST" class="w-100 p-4 border rounded shadow-sm" style="max-width: 500px;">
    <h4 class="mb-3">Daftar</h4>
    
    <!-- Google Register Button -->
    <a href="$BaseHref/auth/google-login" class="btn btn-outline-danger w-100 mb-3 d-flex align-items-center justify-content-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2">
        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
      </svg>
      Daftar dengan Google
    </a>
    
    <div class="position-relative mb-3">
      <hr>
      <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted">atau</span>
    </div>
    
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="register_first_name" class="form-label">Nama Depan</label>
        <input type="text" class="form-control" id="register_first_name" name="register_first_name" required>
      </div>

      <div class="col-md-6 mb-3">
        <label for="register_last_name" class="form-label">Nama Belakang</label>
        <input type="text" class="form-control" id="register_last_name" name="register_last_name" required>
      </div>
    </div>

    <div class="mb-3">
      <label for="register_email" class="form-label">Email</label>
      <input type="email" class="form-control" id="register_email" name="register_email" required>
    </div>

    <div class="mb-3">
      <label for="register_password_1" class="form-label">Sandi</label>
      <input type="password" class="form-control" id="register_password_1" name="register_password_1" required>
    </div>

    <div class="mb-3">
      <label for="register_password_2" class="form-label">Konfirmasi Sandi</label>
      <input type="password" class="form-control" id="register_password_2" name="register_password_2" required>
    </div>

    <button type="submit" class="btn btn-success w-100 mb-3">Daftar</button>
    
    <div class="text-center">
      <p class="mb-0">Sudah memiliki akun? <a href="$BaseHref/auth/login">Masuk di sini</a></p>
    </div>
  </form>
</main>
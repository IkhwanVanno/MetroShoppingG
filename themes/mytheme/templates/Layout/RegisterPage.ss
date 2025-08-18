<main class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <form action="$BaseHref/auth/register" method="POST" class="w-100 p-4 border rounded shadow-sm" style="max-width: 500px;">
    <h4 class="mb-3">Register</h4>
    <% if $ValidationResult %>
      <% if $ValidationResult.isValid %>
        <% loop $ValidationResult.Messages %>
          <div class="alert alert-success" role="alert">$Message</div>
        <% end_loop %>
      <% else %>
        <% loop $ValidationResult.Messages %>
          <div class="alert alert-danger" role="alert">$Message</div>
        <% end_loop %>
      <% end_if %>
    <% end_if %>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="register_first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="register_first_name" name="register_first_name" required>
      </div>

      <div class="col-md-6 mb-3">
        <label for="register_last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="register_last_name" name="register_last_name" required>
      </div>
    </div>

    <div class="mb-3">
      <label for="register_email" class="form-label">Email</label>
      <input type="email" class="form-control" id="register_email" name="register_email" required>
    </div>

    <div class="mb-3">
      <label for="register_password_1" class="form-label">Password</label>
      <input type="password" class="form-control" id="register_password_1" name="register_password_1" required>
    </div>

    <div class="mb-3">
      <label for="register_password_2" class="form-label">Confirm Password</label>
      <input type="password" class="form-control" id="register_password_2" name="register_password_2" required>
    </div>

    <button type="submit" class="btn btn-success w-100 mb-3">Register</button>
    
    <div class="text-center">
      <p class="mb-0">Already have an account? <a href="$BaseHref/auth/login">Login here</a></p>
    </div>
  </form>
</main>
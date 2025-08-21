<main class="profile-page">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2>Profil Saya</h2>
                
                <% if $UpdateSuccess %>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        $UpdateSuccess
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <% end_if %>
                
                <% if $UpdateErrors %>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        $UpdateErrors.RAW
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <% end_if %>
                
                <% if $Member %>
                    <form method="POST" action="$BaseHref/profile" class="profile-form">
                        <input type="hidden" name="SecurityID" value="$SecurityID" />
                        
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Nama Depan *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="$Member.FirstName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nama Belakang *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="$Member.Surname" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" value="$Member.Email" required>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Ubah Sandi (Opsional)</h5>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Sandi Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <small class="form-text text-muted">Diperlukan jika ingin mengubah Sandi</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Sandi Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                            <small class="form-text text-muted">Minimal 8 karakter</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Sandi Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="$BaseHref" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Perbarui Profil</button>
                        </div>
                    </form>
                <% end_if %>
            </div>
        </div>
    </div>
</main>
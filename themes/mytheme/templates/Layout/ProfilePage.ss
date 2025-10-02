<main class="profile-page">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2>Profil Saya</h2>
                    <% if $MembershipTier %>
                        <% with $MembershipTierObject %>
                            <div class="d-flex align-items-center gap-2">
                                <% if $Image %>
                                    <img src="$Image.URL" alt="$Name" width="32" height="32" />
                                <% end_if %>
                                <span class="fw-bold">$Name</span>
                            </div>
                        <% end_with %>
                    <% else %>
                        <span class="text-muted">Member</span>
                    <% end_if %>
                </div>
                
                <% if $MembershipProgress %>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Status Keanggotaan</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p>
                                        <strong>Total Transaksi:</strong> Rp $MembershipProgress.current_total
                                    </p>
                                    <p><strong>Status Saat Ini:</strong> $MembershipProgress.current_tier</p>
                                </div>
                                <div class="col-md-6">
                                    <% if $MembershipProgress.next_tier %>
                                    <p>
                                        <strong>Target Berikutnya:</strong>
                                        $MembershipProgress.next_tier
                                    </p>
                                    <p>
                                        <strong>Sisa:</strong> Rp $MembershipProgress.remaining_amount
                                    </p>

                                    <!-- Progress Bar -->
                                    <div class="progress mb-2" style="height: 20px">
                                        <div
                                            class="progress-bar bg-primary"
                                            role="progressbar"
                                            style="width: $MembershipProgress.progress_percentage;"
                                            aria-valuenow="$MembershipProgress.progress_percentage"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        >
                                            $MembershipProgress.progress_percentage
                                        </div>
                                    </div>
                                    <% else %>
                                    <div class="text-center">
                                        <% if $MembershipTierObject.Image %>
                                            <img
                                                src="$MembershipTierObject.Image.URL"
                                                alt="$MembershipTierObject.Name"
                                                width="48"
                                                height="48"
                                                class="mb-2"
                                            />
                                        <% end_if %>
                                        <p class="text-success fw-bold">
                                            Selamat! Anda telah mencapai tingkat tertinggi!
                                        </p>
                                    </div>
                                    <% end_if %>
                                </div>
                            </div>
                        </div>
                    </div>
                <% end_if %>
                
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
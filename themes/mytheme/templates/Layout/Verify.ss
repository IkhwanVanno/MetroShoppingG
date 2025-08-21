<main class="bg-light d-flex justify-content-center align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
        <div class="card-body text-center">
            <h2 class="card-title mb-4">Verifikasi Email</h2>

            <% if $Content %>
            <div class="alert alert-success">
                $Content.RAW
            </div>
            <% else %>
            <div class="alert alert-danger">
                Token tidak valid atau akun sudah terverifikasi.
            </div>
            <% end_if %>

            <a href="$BaseHref/auth/login" class="btn btn-primary mt-3">Masuk</a>
        </div>
        </div>
    </div>
    </div>
</div>
</main>
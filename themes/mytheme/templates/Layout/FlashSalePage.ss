<main class="container py-5">
    <% if FlashSale %>
    <!-- Flash Sale Status Banner -->
    <div class="container mb-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <span class="badge $FlashSale.StatusBadgeClass me-2">$FlashSale.StatusText</span>
                <% if $FlashSale.isComingSoon %>
                    <span>Flash Sale akan dimulai dalam: <strong id="countdown-to-start"></strong></span>
                <% else_if $FlashSale.isActive %>
                    <span>Flash Sale berakhir dalam: <strong id="countdown-to-end"></strong></span>
                <% else_if $FlashSale.isExpired %>
                    <span>Flash Sale telah berakhir pada: <strong>$FlashSale.End_time.Nice</strong></span>
                <% else %>
                    <span>Flash Sale tidak aktif</span>
                <% end_if %>
            </div>
            <% if $FlashSale.isActive || $FlashSale.isComingSoon %>
                <div class="timer-display bg-dark text-white px-3 py-2 rounded">
                    <div class="d-flex gap-2" id="main-timer">
                        <div class="text-center">
                            <div class="timer-number days fw-bold">00</div>
                            <small>Hari</small>
                        </div>
                        <div class="text-center">
                            <div class="timer-number hours fw-bold">00</div>
                            <small>Jam</small>
                        </div>
                        <div class="text-center">
                            <div class="timer-number minutes fw-bold">00</div>
                            <small>Menit</small>
                        </div>
                        <div class="text-center">
                            <div class="timer-number seconds fw-bold">00</div>
                            <small>Detik</small>
                        </div>
                    </div>
                </div>
            <% end_if %>
        </div>
    </div>

    <!-- Banner Event -->
    <div class="container-fluid p-0 mb-4">
        <div class="position-relative">
            <img
                src="$FlashSale.Image.URL"
                class="img-fluid w-100"
                alt="Banner Event"
                style="max-height: 400px; object-fit: cover;"
            />
            <% if $FlashSale.isActive %>
                <div class="position-absolute top-0 end-0 m-3">
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="fas fa-bolt"></i> FLASH SALE AKTIF
                    </span>
                </div>
            <% end_if %>
        </div>
    </div>

    <!-- Info Event -->
    <div class="container py-4">
        <div class="row">
            <div class="col-md-8">
                <h2 class="mb-3">$FlashSale.Name</h2>
                <p class="text-muted mb-2">
                    <strong>Mulai:</strong> $FlashSale.Start_time.Nice
                </p>
                <p class="text-muted mb-3">
                    <strong>Berakhir:</strong> $FlashSale.End_time.Nice
                </p>
                <p>$FlashSale.Description</p>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title text-danger">Diskon Flash Sale</h5>
                        <h2 class="text-danger fw-bold">$FlashSale.DiscountFlashSale%</h2>
                        <p class="card-text small">
                            <% if $FlashSale.isActive %>
                                Berlaku sekarang!
                            <% else_if $FlashSale.isComingSoon %>
                                Segera dimulai!
                            <% else %>
                                Flash Sale berakhir
                            <% end_if %>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produk Terkait Event -->
    <div class="container pb-5">
        <h4 class="mb-4">Produk Flash Sale</h4>
        <% if $FlashSale.Product %>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4">
                <% loop $FlashSale.Product %>
                    <!-- Product Card -->
                    <div class="col">
                        <a href="{$BaseHref}/list-product/view/{$ID}" class="text-decoration-none">
                            <div class="card h-100 position-relative" style="flex: 1 1 150px; max-width: 100%">
                                <% if $hasDiscount %>
                                <span class="badge bg-danger position-absolute" style="top: 5px; left: 5px; z-index: 1; font-size: 0.6rem;">
                                    $DiscountPercentage
                                </span>
                                <% end_if %>

                                <% if $FlashSale && $FlashSale.isActive %>
                                <span class="badge bg-info position-absolute" style="top: 5px; right: 5px; z-index: 1; font-size: 0.6rem;">
                                    FlashSale $FlashSale.DiscountFlashSale%
                                </span>
                                <% end_if %>

                                <% if $Image %>
                                <img src="$Image.URL" class="card-img-top" alt="$Name" style="height: 150px; object-fit: cover" />
                                <% else %>
                                <img src="https://picsum.photos/200/150?random=$ID" class="card-img-top" alt="$Name" style="height: 150px; object-fit: cover" />
                                <% end_if %>
                                
                                <div class="card-body d-flex flex-column p-2">
                                    <div class="d-flex flex-column flex-grow-1">
                                        <h6 class="card-subtitle mb-1 text-muted" style="font-size: 0.7rem">
                                            <% if $Category %>$Category.Name<% else %>Kategori<% end_if %>
                                        </h6>
                                        <h6 class="card-title mb-2" style="font-size: 0.8rem; line-height: 1.2" title="$Name">
                                            <% if $Name.Length > 20 %>$Name.LimitCharacters(20)...<% else %>$Name<% end_if %>
                                        </h6>
                                    </div>
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <% if $AverageRating %>
                                            <span class="badge bg-warning text-dark me-1" style="font-size: 0.6rem">★ $AverageRating</span>
                                            <% else %>
                                            <span class="badge bg-secondary text-light me-1" style="font-size: 0.6rem">★ 0</span>
                                            <% end_if %>
                                            <small class="text-muted" style="font-size: 0.6rem">$Stok Tersedia</small>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <% if $FlashSale && $FlashSale.isActive %>
                                                <p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
                                                    $FlashSalePrice
                                                </p>
                                                <div class="text-end">
                                                    <small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
                                                        $DisplayPrice
                                                    </small>
                                                    <% if $OriginalPrice %>
                                                    <small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
                                                        $OriginalPrice
                                                    </small>
                                                    <% end_if %>
                                                </div>
                                            <% else %>
                                                <p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
                                                    $DisplayPrice
                                                </p>
                                                <% if $hasDiscount && $OriginalPrice %>
                                                <small class="text-muted text-decoration-line-through" style="font-size: 0.7rem">
                                                    $OriginalPrice
                                                </small>
                                                <% end_if %>
                                            <% end_if %>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <% end_loop %>
            </div>
        <% else %>
            <div class="alert alert-warning text-center">
                <h5>Belum Ada Produk</h5>
                <p class="mb-0">Belum ada produk yang terdaftar dalam flash sale ini.</p>
            </div>
        <% end_if %>
    </div>

    <!-- JavaScript Timer -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashSaleStatus = '$FlashSale.TimerStatus';
        const startTime = '$FlashSale.StartTimeISO';
        const endTime = '$FlashSale.EndTimeISO';
        
        let targetTime;
        let timerElement;
        let textElement;

        // Determine which timer to show
        if (flashSaleStatus === 'coming_soon') {
            targetTime = new Date(startTime).getTime();
            timerElement = document.getElementById('main-timer');
            textElement = document.getElementById('countdown-to-start');
        } else if (flashSaleStatus === 'active') {
            targetTime = new Date(endTime).getTime();
            timerElement = document.getElementById('main-timer');
            textElement = document.getElementById('countdown-to-end');
        }

        if (targetTime && timerElement) {
            function updateTimer() {
                const now = new Date().getTime();
                const distance = targetTime - now;

                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Update main timer display
                    const daysEl = timerElement.querySelector('.days');
                    const hoursEl = timerElement.querySelector('.hours');
                    const minutesEl = timerElement.querySelector('.minutes');
                    const secondsEl = timerElement.querySelector('.seconds');

                    if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
                    if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
                    if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
                    if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');

                    // Update text countdown
                    if (textElement) {
                        let timeText = '';
                        if (days > 0) timeText += `${days} hari `;
                        if (hours > 0) timeText += `${hours} jam `;
                        if (minutes > 0) timeText += `${minutes} menit `;
                        timeText += `${seconds} detik`;
                        textElement.textContent = timeText;
                    }
                } else {
                    // Timer expired, reload page to update status
                    location.reload();
                }
            }

            // Update timer immediately and then every second
            updateTimer();
            setInterval(updateTimer, 1000);
        }
    });
    </script>

    <% end_if %>
</main>
<main class="container py-5">
	<!-- section carausel -->
	<section class="container-fluid px-0">
	<div
		id="tokopediaCarousel"
		class="carousel slide"
		data-bs-ride="carousel"
	>

		<!-- Slides -->
		<div class="carousel-inner">
		<% loop CarouselImage %>
		<div class="carousel-item active">
			<img
				src="$image.URL"
				class="d-block w-100 img-fluid"
				alt="$Name"
				style="max-height: 250px; object-fit: cover"
			/>
		</div>
		<% end_loop %>
		</div>

		<!-- Controls (tombol panah kiri/kanan) -->
		<button
		class="carousel-control-prev"
		type="button"
		data-bs-target="#tokopediaCarousel"
		data-bs-slide="prev"
		>
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Previous</span>
		</button>
		<button
		class="carousel-control-next"
		type="button"
		data-bs-target="#tokopediaCarousel"
		data-bs-slide="next"
		>
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Next</span>
		</button>
	</div>
	</section>

	<!-- About Section -->
	<section class="container py-4">
	<div class="row align-items-center gx-4 gy-4">
		<!-- About Description -->
		<div class="col-12 col-md-6">
		<% if CustomSiteConfig.AboutTitle %>
			<h3 class="fw-bold mb-3">$CustomSiteConfig.AboutTitle</h3>
		<% end_if %>
		<% if CustomSiteConfig.AboutDescription %>
			<p class="mb-0">$CustomSiteConfig.AboutDescription</p>
		<% end_if %>
		</div>

		<!-- Features List -->
		<div class="col-12 col-md-6 ps-md-5">
		<div class="row row-cols-2 g-4">
			<div class="col">
			<% if CustomSiteConfig.SubAbout1Title %>
				<h4 class="fw-semibold mb-1">$CustomSiteConfig.SubAbout1Title</h4>
			<% end_if %>
			<% if CustomSiteConfig.SubAbout1Description %>
				<p class="mb-0">$CustomSiteConfig.SubAbout1Description</p>
			<% end_if %>
			</div>
			<div class="col">
			<% if CustomSiteConfig.SubAbout2Title %>
				<h4 class="fw-semibold mb-1">$CustomSiteConfig.SubAbout2Title</h4>
			<% end_if %>
			<% if CustomSiteConfig.SubAbout2Description %>
				<p class="mb-0">$CustomSiteConfig.SubAbout2Description</p>
			<% end_if %>
			</div>
			<div class="col">
			<% if CustomSiteConfig.SubAbout3Title %>
				<h4 class="fw-semibold mb-1">$CustomSiteConfig.SubAbout3Title</h4>
			<% end_if %>
			<% if CustomSiteConfig.SubAbout3Description %>
				<p class="mb-0">$CustomSiteConfig.SubAbout3Description</p>
			<% end_if %>
			</div>
			<div class="col">
			<% if CustomSiteConfig.SubAbout4Title %>
				<h4 class="fw-semibold mb-1">$CustomSiteConfig.SubAbout4Title</h4>
			<% end_if %>
			<% if CustomSiteConfig.SubAbout4Description %>
				<p class="mb-0">$CustomSiteConfig.SubAbout4Description</p>
			<% end_if %>
			</div>
		</div>
		</div>
	</div>

	<h3 class="mt-5">MetroShoppingG Event</h3>
	<!-- Event Images -->
	<div id="eventCarousel" class="carousel slide" data-bs-ride="carousel">
	<div class="carousel-inner">
		<% loop $EventShopGrouped %>
		<div class="carousel-item<% if $First %> active<% end_if %>">
		<div class="row">
			<% loop $Me %>
			<div class="col-12 col-md-6 position-relative overflow-hidden">
			<a href="$BaseHref/event/$ID" class="text-decoration-none d-block w-100" style="height: 250px;">
				<% if $Image %>
				<img
				src="$Image.URL"
				alt="$Name"
				class="img-fluid w-100 h-100 py-1"
				style="object-fit: cover"
				/>
				<% else %>
				<div class="d-flex align-items-center justify-content-center h-100 bg-light">
				<span class="text-muted">Gambar tidak tersedia</span>
				</div>
				<% end_if %>
			</a>
			</div>
			<% end_loop %>
		</div>
		</div>
		<% end_loop %>
	</div>

	<button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Sebelum</span>
	</button>
	<button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Selanjutnya</span>
	</button>
	</div>
	</section>

	<!-- Vertical Products Section -->
	<section class="container py-4">
		<!-- Category Navigation -->
		<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
			<div>
				<ul class="nav mb-4 flex-wrap gap-2">
					<li class="nav-item">
						<a href="?vertical_category=all" class="btn btn-outline-primary <% if not $VerticalCategoryFilter || $VerticalCategoryFilter == 'all' %>active<% end_if %>">All</a>
					</li>
					<% loop $Category.Limit(5) %>
					<li class="nav-item">
						<a href="?vertical_category=$ID" class="btn btn-outline-primary <% if $Up.VerticalCategoryFilter == $ID %>active<% end_if %>">$Name</a>
					</li>
					<% end_loop %>
				</ul>
			</div>

			<div>
				<ul class="nav justify-content-center mb-4 flex-wrap">
					<li class="nav-item">
						<a class="nav-link active text-secondary-emphasis" href="$BaseHref/list-product">Lihat Semua Produk</a>
					</li>
				</ul>
			</div>
		</div>

		<!-- Product Grid -->
		<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4">
			<% loop $VerticalFilteredProducts.Limit(5) %>
				<div class="col">
					<a href="$BaseHref/list-product/view/{$ID}" class="text-decoration-none">
						<div class="card h-100 position-relative">

							<% if $hasDiscount %>
							<span class="badge bg-danger position-absolute" style="top: 5px; left: 5px; z-index: 1; font-size: 0.6rem;">
								$DiscountPercentage
							</span>
							<% end_if %>

							<% if $hasActiveFlashSale %>
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

									<div class="d-flex align-items-start justify-content-between">
									<% if $hasActiveFlashSale %>
										<p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
											$FlashSalePrice
										</p>
										<div class="text-end">
											<% if $hasDiscount %>
												<small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
													$DisplayPrice
												</small>
											<% end_if %>
											<small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
												$OriginalPrice
											</small>
										</div>
									<% else_if $hasDiscount %>
										<p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
											$DisplayPrice
										</p>
										<small class="text-muted text-decoration-line-through" style="font-size: 0.7rem">
											$OriginalPrice
										</small>
									<% else %>
										<p class="card-text fw-bold text-primary mb-0" style="font-size: 0.9rem">
											$DisplayPrice
										</p>
									<% end_if %>
								</div>
								</div>
							</div>
						</div>
					</a>
				</div>
			<% end_loop %>
			
			<% if not $VerticalFilteredProducts %>
			<div class="col-12">
				<div class="alert alert-info text-center">
					<h5>Produk Tidak Ada</h5>
					<p class="mb-0">Produk Tidak Tersedia Pada Kategori Ini</p>
				</div>
			</div>
			<% end_if %>
		</div>
	</section>

	<h3 class="mt-5">MetroShoppingG FlashSale</h3>
	<!-- Flash Sale Carousel Section -->
	<section class="container-fluid px-0 py-4">
	<div
		id="flashSaleCarousel"
		class="carousel slide"
		data-bs-ride="carousel"
	>

		<!-- Slides -->
		<div class="carousel-inner">
		<% loop FlashSale %>
			<div class="carousel-item<% if $Pos == 1 %> active<% end_if %>">
			<a href="$BaseHref/flashsale/$ID" class="text-decoration-none d-block w-100 position-relative">

				<!-- Gambar Flash Sale -->
				<img
				src="$image.URL"
				class="d-block w-100 img-fluid"
				alt="$Name"
				style="max-height: 250px; object-fit: cover"
				/>

				<!-- Status Badge Overlay -->
				<div class="position-absolute top-0 end-0 m-3">
					<span class="badge $StatusBadgeClass fs-6 px-3 py-2">
						$StatusText
					</span>
				</div>

				<!-- Timer Overlay -->
				<div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-75 text-white text-center py-2">
					<% if $isComingSoon %>
						<div class="small">Flash Sale Dimulai Dalam:</div>
						<div class="d-flex justify-content-center gap-2 fs-6 fw-bold" 
							data-start-time="$StartTimeISO" 
							data-status="coming_soon"
							id="carousel-countdown-$ID">
							<div><span class="days">00</span><small>h</small></div>
							<div><span class="hours">00</span><small>j</small></div>
							<div><span class="minutes">00</span><small>m</small></div>
							<div><span class="seconds">00</span><small>d</small></div>
						</div>
					<% else_if $isActive %>
						<div class="small">Flash Sale Berakhir Dalam:</div>
						<div class="d-flex justify-content-center gap-2 fs-6 fw-bold" 
							data-end-time="$EndTimeISO" 
							data-status="active"
							id="carousel-countdown-$ID">
							<div><span class="days">00</span><small>h</small></div>
							<div><span class="hours">00</span><small>j</small></div>
							<div><span class="minutes">00</span><small>m</small></div>
							<div><span class="seconds">00</span><small>d</small></div>
						</div>
					<% else_if $isExpired %>
						<div class="small text-danger">Flash Sale Telah Berakhir</div>
						<div class="fs-6 fw-bold">
							$End_time.Nice
						</div>
					<% else %>
						<div class="small text-muted">Flash Sale Tidak Aktif</div>
						<div class="fs-6 fw-bold">
							Diskon $DiscountFlashSale%
						</div>
					<% end_if %>
				</div>

			</a>
			</div>
		<% end_loop %>
		</div>
		
		<!-- Controls (tombol panah kiri/kanan) -->
		<button
		class="carousel-control-prev"
		type="button"
		data-bs-target="#flashSaleCarousel"
		data-bs-slide="prev"
		>
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Previous</span>
		</button>
		<button
		class="carousel-control-next"
		type="button"
		data-bs-target="#flashSaleCarousel"
		data-bs-slide="next"
		>
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Next</span>
		</button>
	</div>
	</section>

	<!-- Horizontal Products Section -->
	<section class="container py-4">
		<!-- Category Navigation -->
		<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
			<div>
				<ul class="nav mb-4 flex-wrap gap-2">
					<li class="nav-item">
						<a href="?horizontal_category=all" class="btn btn-outline-primary <% if not $HorizontalCategoryFilter || $HorizontalCategoryFilter == 'all' %>active<% end_if %>">All</a>
					</li>
					<% loop $Category.Limit(5,5) %>
					<li class="nav-item">
						<a href="?horizontal_category=$ID" class="btn btn-outline-primary <% if $Up.HorizontalCategoryFilter == $ID %>active<% end_if %>">$Name</a>
					</li>
					<% end_loop %>
				</ul>
			</div>

			<div>
				<ul class="nav justify-content-center mb-4 flex-wrap">
					<li class="nav-item">
						<a class="nav-link active text-secondary-emphasis" href="$BaseHref/list-product">Lihat Semua Produk</a>
					</li>
				</ul>
			</div>
		</div>

		<!-- Horizontal Product Grid -->
		<div class="row g-4">
			<% loop $HorizontalFilteredProducts.Limit(3) %>
			<div class="col-12 col-md-4">
				<a href="$BaseHref/list-product/view/{$ID}" class="text-decoration-none">
					<div class="card flex-row h-100 position-relative">

						<% if $hasDiscount %>
						<span class="badge bg-danger position-absolute" style="top: 5px; left: 5px; z-index: 1; font-size: 0.6rem;">
							$DiscountPercentage
						</span>
						<% end_if %>

						<% if $hasActiveFlashSale %>
						<span class="badge bg-info position-absolute" style="top: 5px; right: 5px; z-index: 1; font-size: 0.6rem;">
							FlashSale $FlashSale.DiscountFlashSale%
						</span>
						<% end_if %>

						<% if $Image %>
						<img src="$Image.URL" class="img-fluid" style="width: 120px; object-fit: cover" alt="$Name" />
						<% else %>
						<img src="https://picsum.photos/120?random=$ID" class="img-fluid" style="width: 120px; object-fit: cover" alt="$Name" />
						<% end_if %>

						<div class="card-body d-flex flex-column justify-content-between p-2">
							<div>
								<h6 class="text-muted mb-1" style="font-size: 0.7rem">
									<% if $Category %>$Category.Name<% else %>Kategori<% end_if %>
								</h6>
								<h6 class="card-title mb-2" style="font-size: 0.9rem; line-height: 1.2" title="$Name">
									<% if $Name.Length > 25 %>$Name.Left(25)...<% else %>$Name<% end_if %>
								</h6>
							</div>
							<div>
								<div class="d-flex justify-content-between align-items-center mb-1">
									<% if $AverageRating %>
									<span class="badge bg-warning text-dark" style="font-size: 0.6rem">★ $AverageRating</span>
									<% else %>
									<span class="badge bg-secondary text-light" style="font-size: 0.6rem">★ 0</span>
									<% end_if %>
									<small class="text-muted" style="font-size: 0.6rem">$Stok available</small>
								</div>
								<% if $hasActiveFlashSale %>
								<div class="d-flex justify-content-between align-items-start">
									<p class="text-primary fw-bold mb-0" style="font-size: 0.9rem">
										$FlashSalePrice
									</p>
									<div class="text-end">
										<% if $hasDiscount %>
											<small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
												$DisplayPrice
											</small>
										<% end_if %>
										<small class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem">
											$OriginalPrice
										</small>
									</div>
								</div>
								<% else_if $hasDiscount %>
								<div class="d-flex justify-content-between align-items-center">
									<p class="text-primary fw-bold mb-0" style="font-size: 0.9rem">
										$DisplayPrice
									</p>
									<small class="text-muted text-decoration-line-through" style="font-size: 0.7rem">
										$OriginalPrice
									</small>
								</div>
								<% else %>
								<div class="d-flex justify-content-between align-items-center">
									<p class="text-primary fw-bold mb-0" style="font-size: 0.9rem">
										$DisplayPrice
									</p>
								</div>
								<% end_if %>
							</div>
						</div>
					</div>
				</a>
			</div>
			<% end_loop %>

			<% if not $HorizontalFilteredProducts %>
			<div class="col-12">
				<div class="alert alert-info text-center">
					<h5>Produk Tidak Ada</h5>
					<p class="mb-0">Produk Tidak Tersedia Pada Kategori Ini</p>
				</div>
			</div>
			<% end_if %>
		</div>
	</section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
	// Initialize all flash sale timers in carousel
	const timerElements = document.querySelectorAll('[id^="carousel-countdown-"]');
	
	timerElements.forEach(function(timerElement) {
		const status = timerElement.getAttribute('data-status');
		let targetTime;
		
		if (status === 'coming_soon') {
			targetTime = new Date(timerElement.getAttribute('data-start-time')).getTime();
		} else if (status === 'active') {
			targetTime = new Date(timerElement.getAttribute('data-end-time')).getTime();
		}
		
		if (targetTime) {
			function updateCarouselTimer() {
				const now = new Date().getTime();
				const distance = targetTime - now;
				
				if (distance > 0) {
					const days = Math.floor(distance / (1000 * 60 * 60 * 24));
					const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
					const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
					const seconds = Math.floor((distance % (1000 * 60)) / 1000);
					
					// Update timer elements
					const daysEl = timerElement.querySelector('.days');
					const hoursEl = timerElement.querySelector('.hours');
					const minutesEl = timerElement.querySelector('.minutes');
					const secondsEl = timerElement.querySelector('.seconds');
					
					if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
					if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
					if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
					if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
				} else {
					// Timer expired, show expired message
					timerElement.innerHTML = '<div class="text-danger">Flash Sale Berakhir</div>';
				}
			}
			
			// Update timer immediately and then every second
			updateCarouselTimer();
			setInterval(updateCarouselTimer, 1000);
		}
	});
});
</script>
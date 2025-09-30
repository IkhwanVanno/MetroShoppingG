<footer class="bg-light">
<section class="border-top">
<div class="container p-3">
	<div class="row">
	<!-- Kolom Kiri: Brand, Pembayaran, Pengiriman, Sosial Media -->
	<div class="col-lg-6 mb-4">
		<!-- Logo Brand -->
		<h5 class="mb-4 text-center">
		Dipercaya oleh Merek Terkemuka
		</h5>
		<div
		class="row row-cols-3 row-cols-sm-4 row-cols-md-5 g-3 justify-content-center mb-4"
		>
		<% loop Brand %>	
			<div
				class="col d-flex justify-content-center align-items-center"
				>
				<img
				src="$Image.URL"
				class="img-fluid border p-2 bg-white rounded"
				alt="$Name"
				style="max-height: 50px"
				/>
			</div>
		<% end_loop %>
		</div>

		<!-- Metode Pembayaran & Pengiriman (2 kolom) -->
		<div class="row mb-4">
		<!-- Pembayaran -->
		<div class="col-12 col-md-6 mb-3">
			<h6 class="mb-3 text-center">Metode Pembayaran</h6>
			<div class="d-flex flex-wrap justify-content-center gap-3">
				<% loop PaymentMethod %>
					<img
						src="$Image.URL"
						class="img-fluid"
						style="max-height: 40px"
						alt="$Name"
					/>
				<% end_loop %>
			</div>
		</div>

		<!-- Pengiriman -->
		<div class="col-12 col-md-6 mb-3">
			<h6 class="mb-3 text-center">Metode Pengiriman</h6>
			<div class="d-flex flex-wrap justify-content-center gap-3">
				<% loop DeliveryMethod %>
					<img
						src="$Image.URL"
						class="img-fluid"
						style="max-height: 40px"
						alt="$Name"
					/>
				<% end_loop %>
			</div>
		</div>
		</div>

		<!-- Sosial Media -->
		<h6 class="mb-3 text-center">
		Ikuti Kami di Sosial Media
		</h6>
		<div class="d-flex justify-content-around">
			<% loop SocialMedia %>
				<a href="$Link"
					><img
					src="$Image.URL"
					alt="Instagram"
					class="img-fluid"
					style="max-height: 32px"
				/></a>
			<% end_loop %>
		</div>
	</div>

	<!-- Kolom Kanan: Formulir -->
	<div class="col-lg-6">
		<h5 class="mb-3 text-center text-lg-center">Hubungi Kami</h5>
		$ContactForm
	</div>
	</div>
</div>
</section>

<!-- Information Section -->
<section class="border-top">
<div class="container p-3">
	<div class="row g-4">
	<!-- Contact Information -->
	<div class="col-12 col-md-4">
		<h4 class="fw-bold">Informasi Kontak</h4>
		<div class="d-flex align-items-center gap-2 mb-2">
		<img
			style="width: 50px"
			src="$resourceURL('themes/mytheme/images/Headphone.png')"
			alt="support"
		/>
		<div>
			<p class="text-muted mb-0">Ada Pertanyaan? Hubungi kami 24/7</p>
			<% if CustomSiteConfig.Phone %>
				<h5 class="fw-bold mb-0">$CustomSiteConfig.Phone</h5>
			<% end_if %>
		</div>
		</div>
		<% if CustomSiteConfig.Address %>
			<address class="text-muted">$CustomSiteConfig.Address</address>
		<% end_if %>
		<% if CustomSiteConfig.Email %>
			<p class="text-muted">$CustomSiteConfig.Email</p>
		<% end_if %>
	</div>

	<!-- Get to Know Us -->
	<div class="col-6 col-md-2">
		<h5 class="fw-bold">Metode Pengiriman</h5>
		<ul class="list-unstyled">
		<% loop DeliveryMethod %>
			<li>$Name</li>
		<% end_loop %>
		</ul>
	</div>

	<!-- Our Social Media -->
	<div class="col-6 col-md-2">
		<h5 class="fw-bold">Ikuti Kami</h5>
		<ul class="list-unstyled">
		<% loop SocialMedia %>
		<li>
			<a href="$Link" style="text-decoration: none; color: inherit;">
				$Name
			</a>
		</li>
		<% end_loop %>
		</ul>
	</div>

	<!-- Payment Method -->
	<div class="col-12 col-md-4">
		<h5 class="fw-bold">Metode Pembayaran</h5>
		<ul class="list-unstyled">
		<% loop PaymentMethod %>
			<li>$Name</li>
		<% end_loop %>
		</ul>
	</div>
	</div>
</div>
</section>

<!-- Footer Bottom Section -->
<section
class="d-flex flex-column justify-content-center align-items-center p-3 border-top"
>
<% if CustomSiteConfig.Credit %>
	<p class="mb-2 mb-md-0">$CustomSiteConfig.Credit</p>
<% end_if %>
</section>
</footer>
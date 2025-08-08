<Header>
	<section class="border-bottom">
	<nav class="container-fluid row align-items-center py-3 text-center text-lg-start">
		<!-- Logo -->
		<div class="col-12 col-md-3 d-flex justify-content-center justify-content-md-start align-items-center gap-2 mb-2 mb-md-0">
		<a href="$BaseHref">
			<img
			src="$SiteConfig.Logo.URL"
			alt="Logo"
			width="38"
			height="38"
			/>
		</a>
		<div class="text-start">
			<h1 class="fw-bold fs-4 text-uppercase mb-0">$SiteConfig.Title</h1>
			<% if $SiteConfig.Tagline %>
			<small class="text-muted">$SiteConfig.Tagline</small>
			<% end_if %>
		</div>
		</div>

		<!-- Search bar -->
		<div class="col-12 col-md-6">
		<form role="search" action="$BaseHref/list-product-page" method="GET">
			<div class="input-group">
			<input
				type="text"
				name="search"
				class="form-control"
				placeholder="Search"
				aria-label="Search"
				value="$SearchQuery"
			/>

			<button
				class="btn btn-outline-secondary dropdown-toggle"
				type="button"
				data-bs-toggle="dropdown"
				aria-expanded="false"
			>
				<% if $CategoryFilter %>
					<% loop $Category %>
						<% if $ID == $Up.CategoryFilter %>$Name<% end_if %>
					<% end_loop %>
				<% else %>
				All Categories
				<% end_if %>
			</button>
			<ul class="dropdown-menu dropdown-menu-end">
				<li><a class="dropdown-item" href="$BaseHref/list-product-page">All</a></li>
				<% loop $Category %>
				<li><a class="dropdown-item" href="$BaseHref/list-product-page?category=$ID">$Name</a></li>
				<% end_loop %>
			</ul>

			<button class="btn btn-outline-secondary" type="submit">
				<img
				src="$resourceURL('themes/mytheme/images/Search.png')"
				alt="Search"
				width="20"
				height="20"
				/>
			</button>
			</div>
		</form>
		</div>

		<!-- Icons -->
		<div class="col-12 col-md-3 d-flex align-items-center justify-content-center justify-content-md-end gap-3 mt-2 mt-md-0">
		<!-- Cart Link -->
		<% if $IsLoggedIn %>
			<a href="$BaseHref/cart" class="position-relative">
				<img src="$resourceURL('themes/mytheme/images/Cart.png')" alt="Cart" width="24" height="24" />
				<% if $CartItemCount > 0 %>
				<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
					$CartItemCount
				</span>
				<% end_if %>
			</a>
		<% else %>
			<a href="$BaseHref/auth-page/login" class="position-relative">
				<img src="$resourceURL('themes/mytheme/images/Cart.png')" alt="Cart" width="24" height="24" />
			</a>
		<% end_if %>
		
		<!-- Favorite Link -->
		<% if $IsLoggedIn %>
			<a href="$BaseHref/favorite" class="position-relative">
				<img src="$resourceURL('themes/mytheme/images/Heart.png')" alt="Favorite" width="24" height="24" />
				<% if $FavoriteCount > 0 %>
				<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
					$FavoriteCount
				</span>
				<% end_if %>
			</a>
		<% else %>
			<a href="$BaseHref/auth-page/login" class="position-relative">
				<img src="$resourceURL('themes/mytheme/images/Heart.png')" alt="Favorite" width="24" height="24" />
			</a>
		<% end_if %>
		
		<!-- User Dropdown -->
		<div class="dropdown">
			<button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
			<img src="$resourceURL('themes/mytheme/images/User.png')" alt="User" width="24" height="24" />
			<% if $IsLoggedIn %>
				<span class="d-none d-sm-inline">$CurrentUser.FirstName</span>
			<% end_if %>
			</button>
			<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
			<% if $IsLoggedIn %>
				<!-- Jika sudah login -->
				<li><a class="dropdown-item" href="$BaseHref/profile">Profile</a></li>
				<li><a class="dropdown-item" href="$BaseHref/order">Order</a></li>
				<li><hr class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="$BaseHref/Security/logout">Logout</a></li>
			<% else %>
				<!-- Jika belum login -->
				<li><a class="dropdown-item" href="$BaseHref/auth-page/login">Login</a></li>
				<li><a class="dropdown-item" href="$BaseHref/auth-page/register">Register</a></li>
			<% end_if %>
			</ul>
		</div>
		</div>

	</nav>
	</section>
	<%-- <% include Navigation %> --%>
</Header>
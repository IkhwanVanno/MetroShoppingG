<section class="bg-light border-bottom">
  <nav class="container">
    <!-- Tombol toggle menu (jika diperlukan untuk mobile) -->
    <button class="nav-open-button d-md-none">☰</button>

    <!-- Navigasi utama -->
    <ul class="nav justify-content-center flex-column flex-md-row">
      <% loop $Menu(1) %>
        <li class="nav-item">
          <a class="nav-link $LinkingMode text-secondary-emphasis" href="$Link" title="$Title.XML">$MenuTitle.XML</a>
        </li>
      <% end_loop %>
    </ul>
  </nav>
</section>

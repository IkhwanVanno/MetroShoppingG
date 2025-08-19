<form method="get">
  <!-- Preserve existing URL parameters -->
  <% if $SearchQuery %>
  <input type="hidden" name="search" value="$SearchQuery" />
  <% end_if %>
  <% if $CategoryFilter %>
  <input type="hidden" name="category" value="$CategoryFilter" />
  <% end_if %>
  
  <div class="p-3 border rounded bg-light">
    <h5 class="mb-3">Filter Produk</h5>

    <!-- Filter Rating -->
    <div class="mb-3">
      <label class="form-label">Rating Minimum</label>
      <select class="form-select" name="rating">
        <option value=""<% if not $RatingFilter %> selected<% end_if %>>Semua Rating</option>
        <option value="4"<% if $RatingFilter == '4' %> selected<% end_if %>>★★★★☆ 4+ ke atas</option>
        <option value="3"<% if $RatingFilter == '3' %> selected<% end_if %>>★★★☆☆ 3+ ke atas</option>
        <option value="2"<% if $RatingFilter == '2' %> selected<% end_if %>>★★☆☆☆ 2+ ke atas</option>
        <option value="1"<% if $RatingFilter == '1' %> selected<% end_if %>>★☆☆☆☆ 1+ ke atas</option>
      </select>
    </div>

    <!-- Filter Harga (berdasarkan harga final setelah diskon) -->
    <div class="mb-3">
      <label class="form-label">Harga Setelah Diskon (Rp)</label>
      <div class="d-flex gap-2">
        <input type="number" class="form-control" name="min_price" placeholder="Min" value="$MinPriceFilter" min="0">
        <input type="number" class="form-control" name="max_price" placeholder="Max" value="$MaxPriceFilter" min="0">
      </div>
      <small class="text-muted">Harga yang sudah dipotong diskon</small>
    </div>

    <!-- Filter Stock -->
    <div class="mb-3">
      <label class="form-label">Ketersediaan Stok</label>
      <select class="form-select" name="stock">
        <option value=""<% if not $StockFilter %> selected<% end_if %>>Semua Stok</option>
        <option value="available"<% if $StockFilter == 'available' %> selected<% end_if %>>Tersedia (>0)</option>
        <option value="low"<% if $StockFilter == 'low' %> selected<% end_if %>>Stok Menipis (≤10)</option>
      </select>
    </div>

    <!-- Sort By -->
    <div class="mb-3">
      <label class="form-label">Urutkan Berdasarkan</label>
      <select class="form-select" name="sort">
        <option value=""<% if not $SortFilter %> selected<% end_if %>>Default (Terbaru)</option>
        <option value="price_asc"<% if $SortFilter == 'price_asc' %> selected<% end_if %>>Harga Terendah</option>
        <option value="price_desc"<% if $SortFilter == 'price_desc' %> selected<% end_if %>>Harga Tertinggi</option>
        <option value="rating_desc"<% if $SortFilter == 'rating_desc' %> selected<% end_if %>>Rating Tertinggi</option>
        <option value="rating_asc"<% if $SortFilter == 'rating_asc' %> selected<% end_if %>>Rating Terendah</option>
        <option value="name_asc"<% if $SortFilter == 'name_asc' %> selected<% end_if %>>Nama A-Z</option>
        <option value="name_desc"<% if $SortFilter == 'name_desc' %> selected<% end_if %>>Nama Z-A</option>
      </select>
    </div>

    <!-- Tombol Apply Filter -->
    <button type="submit" class="btn btn-primary w-100 mb-2">Terapkan Filter</button>
    
    <!-- Reset Filter Button -->
    <% if $CategoryFilter || $SearchQuery || $MinPriceFilter || $MaxPriceFilter || $SortFilter || $StockFilter || $RatingFilter %>
    <a href="$Link" class="btn btn-outline-secondary w-100">Reset Filter</a>
    <% end_if %>
  </div>
</form>
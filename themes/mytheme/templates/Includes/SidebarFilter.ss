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

    <!-- Filter Stock -->
    <div class="mb-3">
      <label class="form-label">Stok</label>
      <select class="form-select" name="stock">
        <option value=""<% if not $StockFilter %> selected<% end_if %>>Semua Stok</option>
        <option value="available"<% if $StockFilter == 'available' %> selected<% end_if %>>Tersedia (>0)</option>
        <option value="low"<% if $StockFilter == 'low' %> selected<% end_if %>>Stok Menipis (≤10)</option>
      </select>
    </div>

    <!-- Filter Harga -->
    <div class="mb-3">
      <label class="form-label">Harga (Rp)</label>
      <div class="d-flex gap-2">
        <input type="number" class="form-control" name="min_price" placeholder="Min" value="$MinPriceFilter">
        <input type="number" class="form-control" name="max_price" placeholder="Max" value="$MaxPriceFilter">
      </div>
    </div>

    <!-- Sort By -->
    <div class="mb-3">
      <label class="form-label">Urutkan</label>
      <select class="form-select" name="sort">
        <option value=""<% if not $SortFilter %> selected<% end_if %>>Default</option>
        <option value="price_asc"<% if $SortFilter == 'price_asc' %> selected<% end_if %>>Harga Terendah</option>
        <option value="price_desc"<% if $SortFilter == 'price_desc' %> selected<% end_if %>>Harga Tertinggi</option>
        <option value="name_asc"<% if $SortFilter == 'name_asc' %> selected<% end_if %>>Nama A-Z</option>
        <option value="name_desc"<% if $SortFilter == 'name_desc' %> selected<% end_if %>>Nama Z-A</option>
      </select>
    </div>

    <!-- Tombol Apply Filter -->
    <button type="submit" class="btn btn-primary w-100">Terapkan Filter</button>
  </div>
</form>
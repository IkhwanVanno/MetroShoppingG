<main class="container my-4">
    <h3 class="mb-4">Riwayat Pesanan</h3>

    <% if Orders && Orders.Count > 0 %>
        <% loop Orders %>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <strong>$OrderCode</strong>
                        <br />
                        <small class="text-muted">$CreatedAt.Format('d/m/Y')</small>
                    </div>
                    <div class="col-md-2">
                        <span class="badge bg-<% if $Status == 'Pending' %>warning<% else_if $Status == 'Processing' %>info<% else_if $Status == 'Shipped' %>primary<% else_if $Status == 'Delivered' %>success<% else %>secondary<% end_if %>">
                            $Status
                        </span>
                    </div>
                    <div class="col-md-3">
                        <% loop $OrderItems.limit(2) %>
                            <small>$Product.Name <% if not $Last %>, <% end_if %></small>
                        <% end_loop %>
                        <% if $OrderItems.Count > 2 %>
                            <small>dan $OrderItems.Count.subtract(2) lainnya</small>
                        <% end_if %>
                    </div>
                    <div class="col-md-3">
                        <strong>$FormattedTotalAmount</strong>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="$BaseHref/order/detail/$ID" class="btn btn-outline-primary btn-sm">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <% end_loop %>
    <% else %>
        <div class="text-center py-5">
            <h5>Belum ada pesanan</h5>
            <p class="text-muted">Anda belum melakukan pesanan apapun</p>
            <a href="$BaseHref" class="btn btn-primary">Mulai Belanja</a>
        </div>
    <% end_if %>
</main>
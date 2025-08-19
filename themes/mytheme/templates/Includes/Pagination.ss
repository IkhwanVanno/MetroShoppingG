<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <% if $Items.NotFirstPage %>
      <li class="page-item">
        <a class="page-link" href="$Items.PrevLink" aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>
    <% else %>
      <li class="page-item disabled">
        <span class="page-link">&laquo;</span>
      </li>
    <% end_if %>

    <% loop $Items.Pages %>
      <% if $CurrentBool %>
        <li class="page-item active">
          <span class="page-link">$PageNum</span>
        </li>
      <% else %>
        <li class="page-item">
          <a class="page-link" href="$Link">$PageNum</a>
        </li>
      <% end_if %>
    <% end_loop %>

    <% if $Items.NotLastPage %>
      <li class="page-item">
        <a class="page-link" href="$Items.NextLink" aria-label="Next">
          <span aria-hidden="true">&raquo;</span>
        </a>
      </li>
    <% else %>
      <li class="page-item disabled">
        <span class="page-link">&raquo;</span>
      </li>
    <% end_if %>
  </ul>
</nav>
<!-- Multiple Pop-up Advertisement Modals -->
<% if $ShouldShowPopup && $ActivePopups %>
<% loop $ActivePopups %>
<div class="modal fade" id="popupAdModal{$ID}" tabindex="-1" aria-labelledby="popupAdModalLabel{$ID}" aria-hidden="true" data-bs-backdrop="static" data-popup-index="$Pos">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body pb-4">
				<% if $Image.exists %>
					<% if $Link %>
					<a href="$Link" target="_blank">
						<img src="$Image.URL" alt="$Title" class="img-fluid w-100" style="max-height: 80vh; object-fit: contain;">
					</a>
					<% else %>
					<img src="$Image.URL" alt="$Title" class="img-fluid w-100" style="max-height: 80vh; object-fit: contain;">
					<% end_if %>
				<% else %>
					<div class="alert alert-warning m-3">
						<h5>$Title</h5>
						<p>Gambar tidak tersedia</p>
					</div>
				<% end_if %>
			</div>
		</div>
	</div>
</div>
<% end_loop %>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Ambil semua popup modals
	const popupModals = document.querySelectorAll('[id^="popupAdModal"]');
	
	if (popupModals.length > 0) {
		let currentIndex = 0;
		let popupShown = false;
		
		// Fungsi untuk menampilkan popup berdasarkan index
		function showPopupByIndex(index) {
			if (index < popupModals.length) {
				const modalElement = popupModals[index];
				const modal = new bootstrap.Modal(modalElement);
				modal.show();
				
				// Event ketika modal ditutup
				modalElement.addEventListener('hidden.bs.modal', function () {
					currentIndex++;
					showPopupByIndex(currentIndex); // Tampilkan popup berikutnya
				}, { once: true }); // once: true agar event hanya dijalankan sekali
				
				// Event ketika modal ditampilkan (untuk increment counter)
				if (!popupShown) {
					modalElement.addEventListener('shown.bs.modal', function () {
						// Kirim request untuk increment counter
						fetch('$BaseHref/home/incrementPopupView', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
							}
						}).then(response => response.json())
						.then(data => {
							console.log('Popup view counted:', data);
						}).catch(error => {
							console.error('Error counting popup view:', error);
						});
					}, { once: true });
					popupShown = true;
				}
			}
		}
		
		// Tampilkan popup pertama
		showPopupByIndex(0);
	}
});
</script>
<% end_if %>
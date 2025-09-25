<div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Show Modal
            window.addEventListener('show-bs-modal', function (event) {
                const id = event.detail.id;
                const el = document.getElementById(id);
                if (!el) {
                    console.error("Modal not found:", id);
                    return;
                }
                const modal = bootstrap.Modal.getOrCreateInstance(el, {
                    backdrop: event.detail.backdrop ?? true,
                    keyboard: event.detail.keyboard ?? true,
                });
                modal.show();
            });

            // Hide Modal
            window.addEventListener('hide-bs-modal', function (event) {
                const id = event.detail.id;
                const el = document.getElementById(id);
                if (!el) return;
                const modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();
            });
        });
    </script>
</div>

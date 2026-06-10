<footer class="py-6 border-t border-gray-200 bg-white text-center mt-8">
        <p class="text-sm text-gray-500">© Trafa Coffee ♥ by Anak Magang</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    <?php if (isset($_SESSION['swal_msg'])): ?>
    <script>
        Swal.fire({
            icon:  '<?= $_SESSION['swal_msg']['icon'] ?>',
            title: '<?= $_SESSION['swal_msg']['title'] ?>',
            text:  '<?= $_SESSION['swal_msg']['text'] ?>',
        });
    </script>
    <?php unset($_SESSION['swal_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['print_payload'])): ?>
    <script>
        /**
         * Kirim perintah cetak ke shell (kasir/index.php) via window.parent.
         * Shell yang memegang koneksi Bluetooth, bukan halaman ini.
         */
        (async () => {
            try {
                const dataStruk = atob("<?= $_SESSION['print_payload'] ?>");

                if (window.parent && typeof window.parent.printStruk === 'function') {
                    // Cara utama: panggil langsung fungsi di shell
                    await window.parent.printStruk(dataStruk);
                } else {
                    // Fallback: BroadcastChannel (jika dibuka di tab terpisah)
                    new BroadcastChannel('printer_channel').postMessage(dataStruk);
                }
            } catch (e) {
                console.error('Gagal mengirim data cetak:', e);
            }
        })();
    </script>
    <?php unset($_SESSION['print_payload']); ?>
    <?php endif; ?>

</body>
</html>
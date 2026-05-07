    <footer class="py-6 border-t border-gray-200 bg-white text-center transition-all duration-300 lg:ml-[280px] pc-main">
        <p class="text-sm text-gray-500">© Trafa Coffee ♥ by Anak Magang</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('.pc-header');
        const mains = document.querySelectorAll('.pc-main');
        const overlay = document.getElementById('sidebar-overlay');
        const btnToggle = document.getElementById('sidebar-toggle-btn');

        function toggleSidebar() {
          const isMobile = window.innerWidth < 1024;
          if (isMobile) {
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
            overlay.classList.toggle('hidden');
          } else {
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-0');
            mains.forEach(el => el.classList.toggle('lg:ml-0'));
          }
        }

        if(btnToggle) {
            btnToggle.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
        }
        if(overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }

        window.addEventListener('resize', () => {
          if (window.innerWidth >= 1024 && overlay) {
            overlay.classList.add('hidden');
          }
        });
      });
    </script>

    <?php if (isset($_SESSION['swal_msg'])): ?>
      <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal_msg']['icon'] ?>',
            title: '<?= $_SESSION['swal_msg']['title'] ?>',
            text: '<?= $_SESSION['swal_msg']['text'] ?>',
            timer: 3000,
            showConfirmButton: false
        });
      </script>
      <?php unset($_SESSION['swal_msg']); ?>
    <?php endif; ?>

  </body>
</html>
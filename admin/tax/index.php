<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';


$pdo->exec("CREATE TABLE IF NOT EXISTS `tax` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `amount` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`)
)");

$stmt = $pdo->query("SELECT * FROM tax ORDER BY id DESC");
$taxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manajemen Pajak";
$currentPage = "tax";
 
?>

<?php 

include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/sidebar.php';

?>

    <header class="fixed inset-x-0 top-0 z-[1025] flex h-[74px] items-center bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all duration-200 ease-in-out lg:left-[280px]">
      <div class="flex grow items-center sm:px-2">
        <div class="mr-auto">
          <ul class="inline-flex h-[74px] items-center">
            <li class="hidden items-center lg:inline-flex">
              <a href="#" class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="sidebar-hide">
                <i class="fa-solid fa-bars text-lg"></i>
              </a>
            </li>
            <li class="inline-flex items-center lg:hidden">
              <a href="#" class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="mobile-collapse">
                <i class="fa-solid fa-bars text-lg"></i>
              </a>
            </li>
          </ul>
        </div>
        <h1 class="text-lg font-bold text-gray-800 ml-2">Manajemen Pajak</h1>
      </div>
    </header>

    <main class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">  
          
          <?php if(isset($_GET['status'])): ?>
              <?php if($_GET['status'] == 'success'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 transition-opacity duration-500">
                      <div><span class="font-medium">Berhasil!</span> <?php echo htmlspecialchars($_GET['msg']); ?></div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php elseif($_GET['status'] == 'error'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 transition-opacity duration-500">
                      <div><span class="font-medium">Gagal!</span> <?php echo htmlspecialchars($_GET['msg']); ?></div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-red-800 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php endif; ?>
              <script>
                  setTimeout(() => {
                      const alertBox = document.getElementById('alert-message');
                      if(alertBox) {
                          alertBox.style.opacity = '0';
                          setTimeout(() => alertBox.remove(), 500);
                      }
                  }, 4000);
              </script>
          <?php endif; ?>

          <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-gray-800 whitespace-nowrap">Daftar Pajak & Layanan</h3>
                
                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <button data-modal-target="tambah-pajak-modal" data-modal-toggle="tambah-pajak-modal" class="inline-flex w-full sm:w-auto items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 whitespace-nowrap" type="button">
                        <i class="fa-solid fa-plus mr-2"></i>Tambah Pajak
                    </button>
                </div>
              </div>

              <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm text-gray-500">
                  <thead class="bg-gray-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                    <tr>
                      <th scope="col" class="w-20 px-6 py-4 text-center">No</th>
                      <th scope="col" class="px-6 py-4">Nama / Jenis Pajak</th>
                      <th scope="col" class="w-32 px-6 py-4 text-center">Persentase (%)</th>
                      <th scope="col" class="w-32 px-6 py-4 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($taxes)): ?>
                        <tr><td colspan="4" class="px-6 py-6 text-center text-gray-500">Data pajak belum tersedia.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($taxes as $tax): ?>
                        <tr class="border-b border-gray-100 bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center text-gray-500 font-medium"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 font-bold text-gray-900"><?php echo htmlspecialchars($tax['name']); ?></td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600"><?php echo (float)$tax['amount']; ?> %</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            onclick="openEditModal(<?php echo $tax['id']; ?>, '<?php echo htmlspecialchars(addslashes($tax['name'])); ?>', <?php echo (float)$tax['amount']; ?>)" 
                                            class="inline-flex h-8 w-8 items-center justify-center rounded bg-blue-50 text-blue-600 hover:bg-blue-100">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="hapus.php?id=<?php echo $tax['id']; ?>" 
                                       onclick="return confirm('Yakin ingin menghapus <?php echo htmlspecialchars(addslashes($tax['name'])); ?>?')"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 hover:bg-red-100">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

                <div class="mt-4 text-sm text-gray-500">
                    Total Keseluruhan: <span class="font-bold text-gray-900"><?php echo count($taxes); ?> Data</span>
                </div>
          </div>
      </div>
    </main>

    <div id="tambah-pajak-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Pajak Baru</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="tambah-pajak-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <form action="tambah.php" method="POST" class="p-4 md:p-5">
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-900">Nama Pajak</label>
                        <input type="text" name="name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: PPN / Service Charge" required />
                    </div>
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-900">Besaran Persentase (%)</label>
                        <input type="number" step="0.01" name="amount" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: 12 atau 10.5" required />
                    </div>
                    <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        <i class="fa-solid fa-plus mr-2 mt-1"></i> Simpan ke Database
                    </button>
                </form>
            </div>
        </div>
    </div> 

    <button id="trigger-edit-modal" data-modal-target="edit-pajak-modal" data-modal-toggle="edit-pajak-modal" class="hidden"></button>
    <div id="edit-pajak-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Data Pajak</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="edit-pajak-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <form action="edit.php" method="POST" class="p-4 md:p-5">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-900">Nama Pajak</label>
                        <input type="text" name="name" id="edit_name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" required />
                    </div>
                    <div class="mb-6">
                        <label class="mb-2 block text-sm font-medium text-gray-900">Besaran Persentase (%)</label>
                        <input type="number" step="0.01" name="amount" id="edit_amount" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" required />
                    </div>
                    <div class="flex items-center space-x-3 border-t border-gray-100 pt-4 md:pt-5">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                        </button>
                        <button data-modal-hide="edit-pajak-modal" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<?php 

include __DIR__ . '/../layout/footer.php';
?>

<script>
    // 1. Fungsi Kirim Data ke Modal Edit
    function openEditModal(id, name, amount) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_amount').value = amount;
        document.getElementById('trigger-edit-modal').click();
    }

    // 2. Fungsi Responsive Sidebar (Standar)
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('main');

        document.getElementById('sidebar-hide')?.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-[280px]');
            header.classList.toggle('lg:left-0');
            mainContent.classList.toggle('lg:ml-[280px]');
            mainContent.classList.toggle('lg:ml-0');
        });

        document.getElementById('mobile-collapse')?.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
        });
    });
</script>
<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}


$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_sql = "";
$params = [];

if (!empty($search)) {
    $where_sql = " WHERE name LIKE :search";
    $params[':search'] = "%$search%";
}

$count_sql = "SELECT COUNT(*) FROM `table`" . $where_sql;
$total_stmt = $pdo->prepare($count_sql);
$total_stmt->execute($params);
$total_records = $total_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;
$fetch_sql = "SELECT * FROM `table`" . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($fetch_sql);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$mejas = $stmt->fetchAll();
?>

<?php

$pageTitle = "Manajemen Meja";
$currentPage = "meja";

include '../layout/header.php';
include '../layout/sidebar.php';

?>


    <div class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">  
          
          <?php if(isset($_GET['status'])): ?>
              <?php if($_GET['status'] == 'success'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 transition-opacity duration-500">
                      <div><span class="font-medium">Berhasil!</span> Data meja telah diperbarui.</div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php elseif($_GET['status'] == 'error'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 transition-opacity duration-500">
                      <div><span class="font-medium">Gagal!</span> <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan.'; ?></div>
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
                <h3 class="text-xl font-bold text-gray-800">Manajemen Meja</h3>
                <div class="flex items-center gap-4">
                    <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto" id="filter-form">
                        <div class="relative w-full sm:w-auto">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search-input" value="<?php echo htmlspecialchars($search); ?>" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5 outline-none" 
                                   placeholder="Cari nama meja..." autocomplete="off">
                        </div>

                        <input type="hidden" name="page" id="page-input" value="1">
                        <button type="submit" class="hidden">Cari</button>
                    </form>
                    <button data-modal-target="tambah-meja-modal" data-modal-toggle="tambah-meja-modal" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" type="button">
                        <i class="fa-solid fa-plus mr-2"></i>Tambah Meja
                    </button>
                </div>
              </div>

              <div id="tambah-meja-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
                <div class="relative max-h-full w-full max-w-md p-4">
                    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                            <h3 class="text-lg font-semibold text-gray-900">Tambahkan Meja Baru</h3>
                            <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="tambah-meja-modal">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                        
                        <form action="tambah" method="POST" class="p-4 md:p-5">
                            <div class="mb-6">
                                <label for="name" class="mb-2 block text-sm font-medium text-gray-900">Nama Meja</label>
                                <input type="text" name="name" id="name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: Meja 01 / Meja VIP" required />
                            </div>
                            <button type="submit" name="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                <i class="fa-solid fa-plus mr-2 mt-1"></i> Simpan ke Database
                            </button>
                        </form>
                    </div>
                </div>
            </div> 
              
              <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm text-gray-500">
                  <thead class="bg-gray-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                    <tr>
                      <th scope="col" class="w-20 px-6 py-4 text-center">No</th>
                      <th scope="col" class="px-6 py-4">Nama Meja</th>
                      <th scope="col" class="w-32 px-6 py-4 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($mejas)): ?>
                        <tr><td colspan="3" class="py-6 text-center text-gray-500">Data meja belum tersedia.</td></tr>
                    <?php else: ?>
                        <?php 
                        $no = $offset + 1; 
                        foreach ($mejas as $row): 
                        ?>
                        <tr class="border-b border-gray-100 bg-white transition-colors hover:bg-gray-50">
                          <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                          <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                          <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                              <button type="button" 
                                      data-id="<?php echo $row['id']; ?>"
                                      data-nama="<?php echo htmlspecialchars($row['name']); ?>"
                                      onclick="openEditMejaModal(this)"
                                      class="inline-flex h-8 w-8 items-center justify-center rounded bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 hover:text-blue-900">
                                  <i class="fa-solid fa-pen-to-square"></i>
                              </button>
                              
                              <a href="hapus?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus meja ini?')" class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-900">
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

              <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                  <span class="text-sm text-gray-500">
                    <?php
                    $start = ($total_records > 0) ? $offset + 1 : 0;
                    $end = $offset + count($mejas);
                    ?>
                    Menampilkan <span class="font-medium text-gray-900"><?php echo $start; ?> - <?php echo $end; ?></span> dari <span class="font-medium text-gray-900"><?php echo $total_records; ?></span> data
                  </span>
                  
                  <div class="flex space-x-1">
                      <?php if ($page > 1): ?>
                          <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" 
                            class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                      <?php else: ?>
                          <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 hover:bg-gray-50/50 transition-colors"><i class="fa-solid fa-chevron-left text-xs cursor-not-allowed"></i></button>
                            <?php endif; ?>

                      <?php for($i = 1; $i <= $total_pages; $i++): ?>
                          <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" 
                            class="rounded border px-3 py-1.5 text-sm transition-colors <?php echo ($i == $page) ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                          </a>
                      <?php endfor; ?>

                      <?php if ($page < $total_pages): ?>
                          <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" 
                            class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                            <?php else: ?>
                              <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 hover:bg-gray-50/50 transition-colors"><i class="fa-solid fa-chevron-right text-xs cursor-not-allowed"></i></button>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <button id="trigger-edit-meja-modal" data-modal-target="edit-meja-modal" data-modal-toggle="edit-meja-modal" class="hidden"></button>
    <div id="edit-meja-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Nama Meja</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="edit-meja-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form action="edit" method="POST" class="p-4 md:p-5">
                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-6">
                        <label for="edit_name" class="mb-2 block text-sm font-medium text-gray-900">Nama Meja</label>
                        <input type="text" name="name" id="edit_name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" required />
                    </div>
                    
                    <div class="flex items-center space-x-3 border-t border-gray-100 pt-4 md:pt-5">
                        <button type="submit" name="update" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                        </button>
                        <button data-modal-hide="edit-meja-modal" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
      function openEditMejaModal(button) {
        const id = button.getAttribute('data-id');
        const nama = button.getAttribute('data-nama');

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = nama;

        document.getElementById('trigger-edit-meja-modal').click();
      }
    </script>

    <script>
    const searchInput = document.getElementById('search-input');
        let debounceTimer;

        if (searchInput) {
            const val = searchInput.value;
            if (val) {
                searchInput.focus();
                searchInput.setSelectionRange(val.length, val.length);
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                
                debounceTimer = setTimeout(() => {
                    const pageInput = document.getElementById('page-input') || document.querySelector('input[name="page"]');
                    if (pageInput) pageInput.value = 1;
                    
                    const form = document.getElementById('filter-form') || searchInput.closest('form');
                    if (form) form.submit();
                }, 500); 
            });
        }
    </script>
  
  
<?php 
include '../layout/footer.php'; 
?>
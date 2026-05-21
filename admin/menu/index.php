<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}


$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$offset = ($page - 1) * $limit;
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "name LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "category = :category";
    $params[':category'] = $category_filter;
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_conditions);
}

$count_sql = "SELECT COUNT(*) FROM menu" . $where_sql;
$total_stmt = $pdo->prepare($count_sql);
$total_stmt->execute($params);
$total_records = $total_stmt->fetchColumn();

$total_pages = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$fetch_sql = "SELECT * FROM menu" . $where_sql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($fetch_sql);

foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$menu = $stmt->fetchAll();
$query_string = "&search=" . urlencode($search) . "&category=" . urlencode($category_filter);
?>

<?php

$pageTitle = "Manajemen Menu";
$currentPage = "menu";

include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/sidebar.php';

?>


    <div class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">  
          
          <?php if(isset($_GET['status'])): ?>
              <?php if($_GET['status'] == 'success'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 transition-opacity duration-500" role="alert">
                      <div><span class="font-medium">Berhasil!</span> Data menu Trafa Coffee telah diperbarui.</div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php elseif($_GET['status'] == 'error'): ?>
                  <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 transition-opacity duration-500" role="alert">
                      <div><span class="font-medium">Gagal!</span> <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan pada database.'; ?></div>
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
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-gray-800">Manajemen Menu & Harga</h3>
                <div class="flex items-center gap-4">
                    <form method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        
                        <!-- Search Bar -->
                        <div class="relative w-full sm:w-auto">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search-input" value="<?php echo htmlspecialchars($search); ?>" 
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2 outline-none" 
                                   placeholder="Cari menu..." autocomplete="off">
                        </div>

                        <!-- Dropdown Kategori -->
                        <select name="category" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none block w-full sm:w-40 p-2">
                            <option value="">Semua Kategori</option>
                            <option value="1" <?php echo ($category_filter == '1') ? 'selected' : ''; ?>>Makanan</option>
                            <option value="2" <?php echo ($category_filter == '2') ? 'selected' : ''; ?>>Minuman</option>
                        </select>
                        
                        <input type="hidden" name="page" value="1">
                        <button type="submit" class="hidden">Cari</button>
                    </form>
                    <button type="button" data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                        <i class="fa-solid fa-plus mr-2"></i>Tambah Menu
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm text-gray-500">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="w-16 px-6 py-4 text-center">No</th>
                            <th scope="col" class="w-28 px-6 py-4 text-center">Gambar</th>
                            <th scope="col" class="px-6 py-4">Nama Menu</th>
                            <th scope="col" class="px-6 py-4">Kategori</th>
                            <th scope="col" class="px-6 py-4">Harga</th>
                            <th scope="col" class="px-6 py-4">Deskripsi</th> 
                            <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($menu)): ?>
                            <tr><td colspan="7" class="py-6 text-center text-gray-500">Data menu belum tersedia.</td></tr>
                        <?php endif; ?>

                        <?php 
                        $no = $offset + 1;
                        foreach ($menu as $row) : 
                        ?>
                        <tr class="border-b border-gray-100 bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                $img_src = htmlspecialchars($row['image']);
                                if (!preg_match('/^http/', $img_src)) {
                                    $img_src = '../../' . $img_src; 
                                }
                                ?>
                                <img src="<?= $img_src; ?>" alt="Menu" class="w-14 h-14 rounded-lg object-cover mx-auto shadow-sm" onerror="this.src='https://placehold.co/100x100?text=No+Image'">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium <?php echo ($row['category'] == 1 ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700'); ?>">
                                    <?php echo ($row['category'] == 1 ? 'Makanan' : 'Minuman'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-700">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            
                            <td class="px-6 py-4">
                                <p class="max-w-[200px] truncate text-xs text-gray-500">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" 
                                            data-id="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                            data-category="<?php echo $row['category']; ?>"
                                            data-price="<?php echo $row['price']; ?>"
                                            data-image="<?php echo htmlspecialchars($row['image']); ?>"
                                            data-description="<?php echo htmlspecialchars($row['description']); ?>" 
                                            onclick="openEditModal(this)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded bg-blue-50 text-blue-600 transition-colors hover:bg-blue-100 hover:text-blue-900">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus menu ini?')" 
                                       class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-900">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-sm text-gray-500">
                  <?php
                  $start = ($total_records > 0) ? $offset + 1 : 0;
                  $end = $offset + count($menu);
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

    <div id="crud-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Menu Baru</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-toggle="crud-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form action="tambah.php" method="POST" enctype="multipart/form-data" class="p-4 md:p-5">
                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-900">Nama Menu</label>
                            <input type="text" name="name" id="name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: Espresso Single" required>
                        </div>
                        
                        <div class="col-span-2 sm:col-span-1">
                            <label for="price" class="mb-2 block text-sm font-medium text-gray-900">Harga (Rp)</label>
                            <input type="number" name="price" id="price" min="0" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="15000" required>
                        </div>
                        
                        <div class="col-span-2 sm:col-span-1">
                            <label for="category" class="mb-2 block text-sm font-medium text-gray-900">Kategori</label>
                            <select id="category" name="category" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="1">Makanan</option> 
                                <option value="2">Minuman</option> 
                            </select>
                        </div>
                        
                        <div class="col-span-2">
                            <label for="image" class="mb-2 block text-sm font-medium text-gray-900">Gambar</label>
                            <input type="file" name="image" id="image"  accept="image/*" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        
                        <div class="col-span-2">
                            <label for="description" class="mb-2 block text-sm font-medium text-gray-900">Deskripsi Menu</label>
                            <textarea id="description" name="description" rows="3" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Tulis deskripsi menu di sini..." required></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                        <i class="fa-solid fa-plus mr-2"></i> Simpan ke Database
                    </button>
                </form>
            </div>
        </div>
    </div>

    <button id="trigger-edit-modal" data-modal-target="edit-crud-modal" data-modal-toggle="edit-crud-modal" class="hidden"></button>
    <div id="edit-crud-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Menu</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="edit-crud-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <form id="form-edit-menu" action="edit.php" method="POST" enctype="multipart/form-data" class="p-4 md:p-5">
                    <input type="hidden" name="id" id="edit-id">
                    <input type="hidden" name="old_image" id="edit-old-image">

                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900">Nama Menu</label>
                            <input type="text" name="name" id="edit-name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="mb-2 block text-sm font-medium text-gray-900">Harga (Rp)</label>
                            <input type="number" name="price" id="edit-price" min="0" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="mb-2 block text-sm font-medium text-gray-900">Kategori</label>
                            <select name="category" id="edit-category" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                                <option value="1">Makanan</option>
                                <option value="2">Minuman</option>
                            </select>
                        </div>
                        <div class="col-span-2">


                            <label class="mb-2 block text-sm font-medium text-gray-900">Ganti Gambar (Opsional)</label>
                            <input type="file" name="image" accept="image/*" class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 file:mr-4 file:rounded-md file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Biarkan kosong jika tidak ingin mengubah gambar saat ini.</p>

                            <label class="mb-2 block text-sm font-medium text-gray-900">Link URL Gambar</label>
                            <input type="file" name="image" id="edit-image" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>


                        </div>
                        <div class="col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-900">Deskripsi Menu</label>
                            <textarea name="description" id="edit-description" rows="3" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required></textarea>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3 border-t border-gray-100 pt-4 md:pt-5">
                        <button type="submit" name="update" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan
                        </button>
                        <button data-modal-hide="edit-crud-modal" type="button" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 hover:text-gray-900">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    

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

    <script>
    function openEditModal(button) {
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const category = button.getAttribute('data-category');
      const price = button.getAttribute('data-price');
      const image = button.getAttribute('data-image');
      const description = button.getAttribute('data-description'); 

      document.getElementById('edit-id').value = id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-category').value = category;
      document.getElementById('edit-price').value = price;
      document.getElementById('edit-image').value = image;
      document.getElementById('edit-description').value = description; 

      document.getElementById('trigger-edit-modal').click();
    }
    </script>

 <?php 
include '../layout/footer.php'; 
?>
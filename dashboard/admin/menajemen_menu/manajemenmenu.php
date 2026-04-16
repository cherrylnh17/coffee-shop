<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../pages/login.php");
    exit;
}

require_once '../database/koneksi.php';

// Menangkap nilai filter dan mencegah nilai minus/nol
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 5; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$total_stmt = $kon->query("SELECT COUNT(*) FROM menus");
$total_records = $total_stmt->fetchColumn();

// Hitung total halaman
$total_pages = ceil($total_records / $limit);
if ($page > $total_pages) {
    $page = $total_pages;
}

// Mengambil data sesuai limit dan halaman yang dipilih
$stmt = $kon->prepare("SELECT * FROM menus ORDER BY id DESC LIMIT :limit OFFSET :offset");

// Menggunakan bindValue agar aman dari SQL Injection
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$menus = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
  <!-- [Head] start -->
  <head>
    <title>Manajemen Menu | Träffa Coffee</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="../assets/css/style.css" id="main-style-link" />
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
  </head>
  <body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
      <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0 bg-primary-500/40">
        <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 transition-[transform_0.2s_linear] origin-left animate-[2.1s_cubic-bezier(0.65,0.815,0.735,0.395)_0s_infinite_normal_none_running_loader-animate]"></div>
      </div>
    </div>

    <!-- [ Sidebar Menu ] start -->
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header flex items-center py-4 px-6 h-header-height">
          <a href="index.php" class="b-brand flex items-center gap-3">
            <img src="../assets/images/logo-dark.svg" class="w-8 h-8" alt="logo" />
            <span class="badge bg-success-500/10 text-success-500 rounded-full theme-version">Admin Panel</span>
          </a>
        </div>
        <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
          <div class="card pc-user-card mx-[15px] mb-[15px] bg-theme-sidebaruserbg dark:bg-themedark-sidebaruserbg">
            <div class="card-body !p-5">
              <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center font-semibold text-sm shadow-sm">
                  J
                </div>
                  <div class="ml-4 mr-2 grow">
                    <h6 class="mb-0">Admin Kece</h6>
                    <small>Administrator</small>
                  </div>
              </div>
            </div>
          </div>
          <div class="w-full md:w-72 bg-white border-r border-gray-100 min-h-screen shadow-sm">
            <ul class="flex flex-col gap-2 p-4">
              
              <li>
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="laporan.html" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">Laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="managemenu.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex justify-center w-6 text-lg text-white group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-mug-hot"></i></span>
                  <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>

              <li>
                <a href="managekasir.html" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="px-4 py-2 mt-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Authentication</span>
              </li>

              <li>
                <a href="../pages/login.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-red-600 transition-colors"><i class="fa-solid fa-right-from-bracket"></i></span>
                  <span class="font-medium">Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <!-- [ Header Topbar ] start -->
   <header class="pc-header bg-white shadow-sm">
      <div class="header-wrapper flex max-sm:px-[15px] px-[25px] grow">
        <div class="me-auto pc-mob-drp">
          <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
            <li class="pc-h-item pc-sidebar-collapse max-lg:hidden lg:inline-flex">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0 p-3 hover:bg-gray-100 rounded-lg text-gray-600" id="sidebar-hide">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
            <li class="pc-h-item pc-sidebar-popup lg:hidden">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0 p-3 hover:bg-gray-100 rounded-lg text-gray-600" id="mobile-collapse">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- [ Main Content ] start -->
    <div class="pc-container">
      <div class="pc-content">  
          <?php if(isset($_GET['status'])): ?>
              <?php if($_GET['status'] == 'success'): ?>
                  <div id="alert-message" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex justify-between items-center transition-opacity duration-500" role="alert">
                      <div><span class="font-medium">Berhasil!</span> Data menu Trafa Coffee telah diperbarui.</div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php elseif($_GET['status'] == 'error'): ?>
                  <div id="alert-message" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 flex justify-between items-center transition-opacity duration-500" role="alert">
                      <div><span class="font-medium">Gagal!</span> <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan pada database.'; ?></div>
                      <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-red-800 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
                  </div>
              <?php endif; ?>
              <script>
                  setTimeout(() => {
                      const alertBox = document.getElementById('alert-message');
                      if(alertBox) {
                          alertBox.style.opacity = '0';
                          setTimeout(() => alertBox.remove(), 500); // Hapus elemen setelah animasi transisi selesai
                      }
                  }, 4000);
              </script>
          <?php endif; ?>
          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-xl text-gray-800">Manajemen Menu & Harga</h3>
                <div class="flex items-center gap-4">
                    <form method="GET" class="flex items-center">
                        <label class="text-sm mr-2">Tampilkan:</label>
                        <select name="limit" onchange="this.form.submit()" class="border-gray-300 rounded text-sm py-1">
                            <option value="5" <?php echo ($limit == 5) ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo ($limit == 25) ? 'selected' : ''; ?>>25</option>
                        </select>
                        <input type="hidden" name="page" value="1">
                    </form>
                    <button type="button" data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i>Tambah Menu
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                        <tr>
                            <th scope="col" class="px-6 py-3 w-16 text-center">No</th>
                            <th scope="col" class="px-6 py-3 w-28 text-center">Gambar</th>
                            <th scope="col" class="px-6 py-3">Nama Menu</th>
                            <th scope="col" class="px-6 py-3">Kategori</th>
                            <th scope="col" class="px-6 py-3">Harga</th>
                            <th scope="col" class="px-6 py-3">Deskripsi</th> 
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($menus)): ?>
                            <tr><td colspan="6" class="text-center py-4">Data menu belum tersedia.</td></tr>
                        <?php endif; ?>

                        <?php 
                        $no = $offset + 1;
                        foreach ($menus as $row) : 
                        ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                            <td class="px-6 py-4 text-center">
                                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Menu" class="w-14 h-14 rounded-lg object-cover mx-auto shadow-sm" onerror="this.src='https://placehold.co/100x100?text=No+Image'">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="px-6 py-4"><?php echo ($row['category'] == 1 ? 'Makanan' : 'Minuman'); ?></td>
                            <td class="px-6 py-4">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            
                            <td class="px-6 py-4 max-w-xs truncate text-xs text-gray-500">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <button type="button" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                        data-category="<?php echo $row['category']; ?>"
                                        data-price="<?php echo $row['price']; ?>"
                                        data-image="<?php echo htmlspecialchars($row['image']); ?>"
                                        data-description="<?php echo htmlspecialchars($row['description']); ?>" 
                                        onclick="openEditModal(this)"
                                        class="inline-block text-blue-600 hover:text-blue-900 mx-1 bg-blue-50 p-2 rounded transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <a href="hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?')" class="inline-block text-red-600 hover:text-red-900 mx-1 bg-red-50 p-2 rounded transition-colors"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-between items-center mt-4">
                <span class="text-sm text-gray-500">
                  <?php
                  $start = ($total_records > 0) ? $offset + 1 : 0;
                  $end = $offset + count($menus);
                  ?>
                  Menampilkan <?php echo $start; ?> - <?php echo $end; ?> dari <?php echo $total_records; ?> data</span>
                <div class="flex space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" 
                          class="px-3 py-1 border rounded text-gray-600">Prev</a>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" 
                          class="px-3 py-1 border rounded <?php echo ($i == $page) ? 'bg-blue-600 text-white' : 'text-gray-600'; ?>">
                          <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" 
                          class="px-3 py-1 border rounded text-gray-600">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
      </div>
    </div>

    <!-- Modal Create New Product -->
    <div id="crud-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white shadow-lg border border-gray-200 rounded-xl p-4 md:p-6">
                <div class="flex items-center justify-between border-b pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Tambah Menu Baru</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="crud-modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <form action="tambah.php" method="POST" class="p-4 md:p-5">
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Menu</label>
                            <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="Contoh: Espresso Single" required>
                        </div>
                        
                        <div class="col-span-2 sm:col-span-1">
                            <label for="price" class="block mb-2 text-sm font-medium text-gray-900">Harga (Rp)</label>
                            <input type="number" name="price" id="price" min="0" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="15000" required>
                        </div>
                        
                        <div class="col-span-2 sm:col-span-1">
                            <label for="category" class="block mb-2 text-sm font-medium text-gray-900">Kategori</label>
                            <select id="category" name="category" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="" disabled selected>Pilih Kategori</option>
                                <option value="1">Makanan</option> <option value="2">Minuman</option> </select>
                        </div>
                        
                        <div class="col-span-2">
                            <label for="image" class="block mb-2 text-sm font-medium text-gray-900">Link URL Gambar</label>
                            <input type="url" name="image" id="image" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="https://image-link.com/photo.jpg" required>
                        </div>
                        
                        <div class="col-span-2">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Deskripsi Menu</label>
                            <textarea id="description" name="description" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-600 focus:border-blue-600 block w-full p-2.5" placeholder="Tulis deskripsi menu di sini..." required></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit" class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center w-full justify-center">
                        <i class="fa-solid fa-plus mr-2"></i> Simpan ke Database
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Product -->
    <button id="trigger-edit-modal" data-modal-target="edit-crud-modal" data-modal-toggle="edit-crud-modal" class="hidden"></button>
      <div id="edit-crud-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white shadow-lg shadow-slate-200 border border-default rounded-base shadow-sm p-4 md:p-6 rounded-xl">
                <div class="flex items-center justify-between border-b border-default pb-4 md:pb-5">
                    <h3 class="text-lg font-medium text-heading">Edit Menu</h3>
                    <button type="button" class="text-body bg-transparent hover:bg-gray-200 hover:text-heading rounded-base text-sm w-9 h-9 ms-auto inline-flex justify-center items-center" data-modal-hide="edit-crud-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                
                <form id="form-edit-menu" action="edit.php" method="POST">
                    <input type="hidden" name="id" id="edit-id">

                    <div class="grid gap-4 grid-cols-2 py-4 md:py-6">
                        <div class="col-span-2">
                            <label class="block mb-2.5 text-sm font-medium text-heading">Nama Menu</label>
                            <input type="text" name="name" id="edit-name" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2.5 text-sm font-medium text-heading">Harga</label>
                            <input type="number" name="price" id="edit-price" min="0" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5" required="">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block mb-2.5 text-sm font-medium text-heading">Kategori</label>
                            <select name="category" id="edit-category" class="block w-full px-3 py-2.5 bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500" required="">
                                <option value="1">Makanan</option>
                                <option value="2">Minuman</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block mb-2.5 text-sm font-medium text-heading">Link URL Gambar</label>
                            <input type="url" name="image" id="edit-image" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5" required="">
                        </div>
                        <div class="col-span-2">
                            <label class="block mb-2.5 text-sm font-medium text-heading">Deskripsi Menu</label>
                            <textarea name="description" id="edit-description" rows="3" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-3 py-2.5" required></textarea>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 border-t border-default pt-4 md:pt-6">
                        <button type="submit" name="update" class="inline-flex items-center text-white bg-blue-600 hover:bg-blue-700 rounded-lg text-sm px-4 py-2.5 transition-colors">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                        </button>
                        <button data-modal-hide="edit-crud-modal" type="button" class="text-gray-700 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg text-sm px-4 py-2.5 transition-colors">Batal</button>
                    </div>
                </form>
            </div>
        </div>
      </div>

    <!-- [ Footer ] start -->
    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid mx-10">
        <div class="flex justify-center items-center gap-1.5">
            <p class="m-0">© Trafa Coffe ♥ by Team Phoenixcoded</p>
        </div>
      </div>
    </footer>
 
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
    function openEditModal(button) {
      const id = button.getAttribute('data-id');
      const name = button.getAttribute('data-name');
      const category = button.getAttribute('data-category');
      const price = button.getAttribute('data-price');
      const image = button.getAttribute('data-image');
      const description = button.getAttribute('data-description'); // Ambil deskripsi

      document.getElementById('edit-id').value = id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-category').value = category;
      document.getElementById('edit-price').value = price;
      document.getElementById('edit-image').value = image;
      document.getElementById('edit-description').value = description; // Masukkan ke textarea

      document.getElementById('trigger-edit-modal').click();
  }
      </script>

  </body>
</html>
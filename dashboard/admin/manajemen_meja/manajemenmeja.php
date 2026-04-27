<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

require_once '../../../config.php';

$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 5; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT COUNT(*) FROM `table`");
$total_records = $total_stmt->fetchColumn();

$total_pages = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$stmt = $pdo->prepare("SELECT * FROM `table` ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$mejas = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>Manajemen Meja | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    
    <link rel="icon" href="../../../assets/image/favicon.svg" type="image/x-icon" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  <body class="bg-gray-50 text-gray-800">

    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-200 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full">
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="../index.php" class="flex items-center gap-3">
            <img src="../../../assets/image/logo.svg" class="h-8 w-8" alt="logo" onerror="this.src='https://placehold.co/32x32?text=Logo'" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Admin Panel</span>
          </a>
        </div>

        <div class="h-[calc(100vh-74px)] overflow-y-auto py-3">
          <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
            <div class="flex items-center">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm">
                AK
              </div>
              <div class="ml-3 mr-2 grow">
                <h6 class="mb-0 text-sm font-semibold text-gray-800">Admin Kece</h6>
                <small class="text-xs text-gray-500">Administrator</small>
              </div>
            </div>
          </div>

          <div class="w-full">
            <ul class="flex flex-col gap-1.5 px-4 py-2">
              
              <li>
                <a href="../index.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="../laporan/laporan.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">Laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="../manajemen_menu/manajemenmenu.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-mug-hot"></i></span>
                  <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>

              <li>
                <a href="manajemenmeja.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-chair"></i></span>
                  <span class="font-medium">Manajemen Meja</span>
                </a>
              </li>

              <li>
                <a href="../manajemen_kasir/manajemenkasir.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="../../../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-red-600"><i class="fa-solid fa-right-from-bracket"></i></span>
                  <span class="font-medium">Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

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
      </div>
    </header>

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
                    <form method="GET" class="flex items-center">
                        <label class="mr-2 text-sm text-gray-600">Tampilkan:</label>
                        <select onchange="setLimit(this.value)" class="appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-8 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                          <option value="5">5</option>
                          <option value="10">10</option>
                          <option value="25">25</option>
                        </select>
                        <input type="hidden" name="page" value="1">
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
                        
                        <form action="tambah.php" method="POST" class="p-4 md:p-5">
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
                              
                              <a href="hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus meja ini?')" class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-900">
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
                          <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">Prev</a>
                          <?php else: ?>
                            <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 hover:bg-gray-50/50 transition-colors"><i class="fa-solid fa-chevron-left text-xs cursor-not-allowed"></i></button>
                      <?php endif; ?>

                      <?php for($i = 1; $i <= $total_pages; $i++): ?>
                          <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>" class="rounded-lg border px-3 py-1.5 text-sm transition-colors <?php echo ($i == $page) ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                          </a>
                      <?php endfor; ?>

                      <?php if ($page < $total_pages): ?>
                          <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">Next</a>
                          <? else: ?>
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
                
                <form action="edit.php" method="POST" class="p-4 md:p-5">
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

    <footer class="relative ml-0 mt-[74px] z-[995] py-[20px] border-t border-gray-200 bg-white transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="mx-auto px-6">
        <div class="flex items-center justify-center gap-1.5 text-sm text-gray-500">
            <p class="m-0">© Trafa Coffee ♥ by Team Phoenixcoded</p>
        </div>
      </div>
    </footer>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('header').nextElementSibling;
        const footer = document.querySelector('footer');
        const btnDesktop = document.getElementById('sidebar-hide');
        const btnMobile = document.getElementById('mobile-collapse');

        const overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.className = 'fixed inset-0 z-[1024] bg-gray-900/40 backdrop-blur-sm hidden transition-opacity duration-200';
        document.body.appendChild(overlay);

        const closeSidebarMobile = () => {
          sidebar.classList.add('max-lg:-left-[280px]');
          sidebar.classList.remove('max-lg:left-0');
          overlay.classList.add('hidden');
          document.body.style.overflow = '';
        };

        const openSidebarMobile = () => {
          sidebar.classList.remove('max-lg:-left-[280px]');
          sidebar.classList.add('max-lg:left-0');
          overlay.classList.remove('hidden');
          document.body.style.overflow = 'hidden';
        };

        if (btnDesktop) {
          btnDesktop.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-[280px]');
            header.classList.toggle('lg:left-0');
            mainContent.classList.toggle('lg:ml-[280px]');
            mainContent.classList.toggle('lg:ml-0');
            footer.classList.toggle('lg:ml-[280px]');
            footer.classList.toggle('lg:ml-0');
          });
        }

        if (btnMobile) {
          btnMobile.addEventListener('click', function(e) {
            e.preventDefault();
            const isOpen = sidebar.classList.contains('max-lg:left-0');
            if (isOpen) {
              closeSidebarMobile();
            } else {
              openSidebarMobile();
            }
          });
        }

        overlay.addEventListener('click', closeSidebarMobile);

        const navLinks = sidebar.querySelectorAll('ul li a');
        navLinks.forEach(link => {
          link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
              closeSidebarMobile();
            }
          });
        });

        window.addEventListener('resize', function() {
          if (window.innerWidth >= 1024) {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
          }
        });
      });
    </script>
  </body>
</html>
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

require_once '../../../config.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username != 'admin' ORDER BY id DESC");
    $stmt->execute();
    $kasirs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>Manajemen Kasir | Träffa Coffee</title>
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
                <a href="../manajemen_meja/manajemenmeja.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-chair"></i></span>
                  <span class="font-medium">Manajemen Meja</span>
                </a>
              </li>

              <li>
                <a href="manajemenkasir.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-users-gear"></i></span>
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
                      <div><span class="font-medium">Berhasil!</span> Aksi berhasil dilakukan.</div>
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
                <h3 class="text-xl font-bold text-gray-800">Manajemen Akun Kasir</h3>
                <button data-modal-target="authentication-modal" data-modal-toggle="authentication-modal" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" type="button">
                    <i class="fa-solid fa-user-plus mr-2"></i>Tambah Kasir
                </button>
              </div>

              <div id="authentication-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
                <div class="relative max-h-full w-full max-w-md p-4">
                    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                            <h3 class="text-lg font-semibold text-gray-900">Tambahkan Akun Kasir</h3>
                            <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="authentication-modal">
                                <i class="fa-solid fa-xmark text-lg"></i>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        
                        <form action="tambah.php" method="POST" class="p-4 md:p-5">
                            <div class="mb-4">
                                <label for="name" class="mb-2 block text-sm font-medium text-gray-900">Nama Lengkap</label>
                                <input type="text" name="name" id="name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Masukkan Nama Lengkap" required />
                            </div>
                            <div class="mb-4">
                                <label for="username" class="mb-2 block text-sm font-medium text-gray-900">Username</label>
                                <input type="text" name="username" id="username" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: kasir_budi" required />
                            </div>
                            <div class="mb-4">
                                <label for="password" class="mb-2 block text-sm font-medium text-gray-900">Password</label>
                                <input type="password" name="password" id="password" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="•••••••••" required />
                            </div>
                            <div class="mb-6">
                                <label for="konfirmasi_password" class="mb-2 block text-sm font-medium text-gray-900">Ulangi Password</label>
                                <input type="password" name="konfirmasi_password" id="konfirmasi_password" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="•••••••••" required />
                            </div>
                            <button type="submit" name="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                Daftarkan Kasir
                            </button>
                        </form>
                    </div>
                </div>
            </div> 
              
              <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm text-gray-500">
                  <thead class="bg-gray-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                    <tr>
                      <th scope="col" class="w-16 px-6 py-4 text-center">No</th>
                      <th scope="col" class="px-6 py-4">Nama</th>
                      <th scope="col" class="px-6 py-4">Username</th>
                      <th scope="col" class="px-6 py-4">Role</th>
                      <th scope="col" class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($kasirs)): ?>
                        <tr><td colspan="4" class="py-6 text-center text-gray-500">Data kasir belum tersedia.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($kasirs as $row): ?>
                        <tr class="border-b border-gray-100 bg-white transition-colors hover:bg-gray-50">
                          <td class="px-6 py-4 text-center"><?php echo $no++; ?></td>
                          <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['name'] ?? '-'); ?></td>
                          <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($row['username']); ?></td>
                          <td class="px-6 py-4">
                              <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Kasir</span>
                          </td>
                          <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                              <a href="hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus akun kasir ini?')" class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-900">
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
        if (btnDesktop && sidebar && header && mainContent && footer) {
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

        const btnMobile = document.getElementById('mobile-collapse');
        if (btnMobile && sidebar) {
          btnMobile.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
          });
        }
      });
    </script>
  </body>
</html>
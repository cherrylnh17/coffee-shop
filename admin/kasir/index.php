<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$name = $_SESSION['name'];
  $words = explode(" ", $name);
  $initials = "";

  foreach ($words as $w) {
    $initials .= mb_substr($w, 0, 1);
  }
  $initials = strtoupper(substr($initials, 0, 2));

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE username != 'admin' ORDER BY id DESC");
    $stmt->execute();
    $kasirs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Gagal mengambil data: " . $e->getMessage();
}
?>

<?php

$pageTitle = "Manajemen Kasir";
$currentPage = "kasir";

include __DIR__ . '/../layout/header.php'; 
include __DIR__ . '/../layout/sidebar.php';

?>


    <main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
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

              <div id="authentication-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
                <div class="relative max-h-full w-full max-w-md p-4">
                    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                            <h3 class="text-lg font-semibold text-gray-900">Tambahkan Akun Kasir</h3>
                            <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="authentication-modal">
                                <i class="fa-solid fa-xmark text-lg"></i>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        
                        <form action="tambah" method="POST" class="p-4 md:p-5">
                            <div class="mb-4">
                                <label for="name" class="mb-2 block text-sm font-medium text-gray-900">Nama Lengkap</label>
                                <input type="text" name="name" id="name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Masukkan Nama Lengkap" required oninput="validateForm()" />
                            </div>
                            <div class="mb-4">
                                <label for="username" class="mb-2 block text-sm font-medium text-gray-900">Username</label>
                                <input type="text" name="username" id="username" class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Contoh: kasir_budi" required oninput="validateForm()" />
                            </div>
                            <div class="mb-4">
                                <label for="password" class="mb-2 block text-sm font-medium text-gray-900">Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" 
                                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" 
                                           placeholder="•••••••••" required oninput="validateForm()" />
                                    <button type="button" onclick="togglePassword('password', 'eye-password')"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors">
                                        <i id="eye-password" class="fa-solid fa-eye text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label for="konfirmasi_password" class="mb-2 block text-sm font-medium text-gray-900">Ulangi Password</label>
                                <div class="relative">
                                    <input type="password" name="konfirmasi_password" id="konfirmasi_password" 
                                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" 
                                           placeholder="•••••••••" required oninput="validateForm()" />
                                    <button type="button" onclick="togglePassword('konfirmasi_password', 'eye-konfirmasi')"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 transition-colors">
                                        <i id="eye-konfirmasi" class="fa-solid fa-eye text-sm"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Pesan validasi password -->
                            <p id="password-msg" class="mb-4 text-xs hidden"></p>

                            <button type="submit" name="submit" id="btn-submit"
                                    disabled
                                    class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
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
                          <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                              <a href="hapus?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus akun kasir ini?')" class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 transition-colors hover:bg-red-100 hover:text-red-900">
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
    </main>

    
  

    <script>
    // Show/hide password toggle
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Validasi form — aktifkan tombol hanya kalau semua terisi & password cocok
    function validateForm() {
        const name     = document.getElementById('name').value.trim();
        const username = document.getElementById('username').value.trim();
        const pass     = document.getElementById('password').value;
        const konfirm  = document.getElementById('konfirmasi_password').value;
        const btn      = document.getElementById('btn-submit');
        const msg      = document.getElementById('password-msg');

        const allFilled = name && username && pass && konfirm;

        if (!allFilled) {
            msg.textContent = '';
            msg.className   = 'mb-4 text-xs hidden';
            btn.disabled    = true;
            return;
        }

        if (pass !== konfirm) {
            msg.textContent = '⚠ Password dan konfirmasi tidak cocok.';
            msg.className   = 'mb-4 text-xs text-red-500 block';
            // Reset border konfirmasi
            document.getElementById('konfirmasi_password').classList.add('border-red-400');
            document.getElementById('konfirmasi_password').classList.remove('border-gray-300');
            btn.disabled = true;
        } else {
            msg.textContent = '✓ Password cocok.';
            msg.className   = 'mb-4 text-xs text-green-600 block';
            document.getElementById('konfirmasi_password').classList.remove('border-red-400');
            document.getElementById('konfirmasi_password').classList.add('border-gray-300');
            btn.disabled = false;
        }
    }
    </script>

<?php 
include __DIR__ . '/../layout/footer.php'; 
?>
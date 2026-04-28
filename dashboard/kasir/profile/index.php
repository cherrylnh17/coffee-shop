<?php
session_start();
require_once '../../../path.php';

if (!isset($_SESSION['name']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

require_once '../../../config.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($old_password !== $user['password']) {
            $error = "Kata sandi lama yang Anda masukkan salah!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi kata sandi baru tidak cocok!";
        } elseif (strlen($new_password) < 8) {
            $error = "Kata sandi baru harus memiliki minimal 8 karakter!";
        } else {
            $update_stmt = $pdo->prepare("UPDATE user SET password = ?, updated_at = NOW() WHERE id = ?");
            if ($update_stmt->execute([$new_password, $user_id])) {
                $success = "Kata sandi berhasil diubah!";
                $user['password'] = $new_password;
            } else {
                $error = "Gagal mengubah kata sandi di database.";
            } 
        }
    }
} catch (PDOException $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}

$nama_lengkap = !empty($user['name']) ? $user['name'] : $user['username'];
$inisial = strtoupper(substr($nama_lengkap, 0, 2));

?>

<?php 

$pageTitle = "Check Pesanan";
$currentPage = "akun";

include '../layout/header.php';
include '../layout/sidebar.php';
?>

    <div class="mt-12 ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">
        
        <div class="w-full max-w-4xl mx-auto">
          
          <!-- Alert System -->
          <?php if($success): ?>
              <div id="alert-message" class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 transition-opacity duration-500">
                  <div><span class="font-medium">Berhasil!</span> <?php echo $success; ?></div>
                  <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
              </div>
          <?php elseif($error): ?>
              <div id="alert-message" class="mb-6 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 transition-opacity duration-500">
                  <div><span class="font-medium">Gagal!</span> <?php echo htmlspecialchars($error); ?></div>
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

          <!-- [ breadcrumb ] start -->
          <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-1">Pengaturan Akun</h2>
                <p class="text-sm text-gray-500">Kelola informasi profil dan keamanan akun kasir Anda.</p>
              </div>
              <ul class="flex items-center gap-2 text-sm text-gray-500">
                  <li><a href="../index.php" class="hover:text-blue-600 transition-colors"><i class="fa-solid fa-house"></i></a></li>
                  <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                  <li class="text-gray-800 font-medium">Tentang Akun</li>
              </ul>
            </div>
          </div>
          <!-- [ breadcrumb ] end -->

          <div class="flex flex-col gap-6">
            
            <!-- [ Kolom Atas: Profil Kasir ] -->
            <div class="space-y-6">
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
                <div class="flex flex-col items-center text-center">
                  <!-- Avatar -->
                  <div class="w-[88px] h-[88px] rounded-full bg-blue-100 border-[6px] border-blue-50 shadow-sm flex items-center justify-center mb-4">
                      <span class="text-blue-600 text-3xl font-bold"><?php echo $inisial; ?></span>
                  </div>
                  
                  <!-- Nama & Role -->
                  <h3 class="text-xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($nama_lengkap); ?></h3>
                  <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-md text-xs font-medium bg-blue-50 text-blue-700 mt-2 mb-8">
                    <i class="fa-solid fa-cash-register"></i> Kasir Träffa Coffee
                  </span>

                  <!-- Username Info -->
                  <div class="w-full text-left">
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-2">Username login saat ini</label>
                    <div class="flex items-center gap-2 text-gray-800 font-medium bg-gray-50 px-4 py-3 border border-gray-200 rounded-xl shadow-sm">
                        <i class="fa-solid fa-user text-gray-400"></i> @<?php echo htmlspecialchars($user['username']); ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- [ Kolom Bawah: Form Ubah Sandi ] -->
            <div>
              <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                <!-- Card Header -->
                <div class="border-b border-gray-100 px-6 py-6">
                  <h4 class="text-lg font-bold text-gray-900 flex items-center gap-3">
                    <i class="fa-solid fa-key text-orange-400 text-xl"></i>
                    Ubah Kata Sandi
                  </h4>
                  <p class="text-sm text-gray-500 mt-2">Pastikan akun Anda menggunakan kata sandi yang kuat untuk menjaga keamanan data toko.</p>
                </div>
                
                <!-- Card Body (Form) -->
                <div class="p-6 sm:p-8">
                  <form action="" method="POST" class="w-full">
                    
                    <!-- Sandi Lama -->
                    <div class="mb-6">
                      <label for="old_password" class="block text-sm font-bold text-gray-700 mb-2">Kata Sandi Lama</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                          <i class="fa-solid fa-lock text-gray-400 text-sm"></i>
                        </div>
                        <input type="password" id="old_password" name="old_password" class="block w-full pl-11 pr-4 py-3 bg-yellow-50/60 border border-yellow-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all" placeholder="Masukkan sandi lama Anda" required>
                      </div>
                    </div>

                    <!-- Sandi Baru -->
                    <div class="mb-6">
                      <label for="new_password" class="block text-sm font-bold text-gray-700 mb-2">Kata Sandi Baru</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                          <i class="fa-solid fa-shield-halved text-gray-400 text-sm"></i>
                        </div>
                        <input type="password" id="new_password" name="new_password" class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all" placeholder="Buat sandi baru" required>
                      </div>
                      <ul class="mt-2 text-xs text-gray-500 space-y-1 list-disc list-inside ml-1">
                          <li>Minimal 8 karakter</li>
                      </ul>
                    </div>

                    <!-- Ulangi Sandi Baru -->
                    <div class="mb-10">
                      <label for="confirm_password" class="block text-sm font-bold text-gray-700 mb-2">Ulangi Kata Sandi Baru</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                          <i class="fa-solid fa-check-double text-gray-400 text-sm"></i>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all" placeholder="Ketik ulang sandi baru" required>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                      <a href="../index.php" class="px-6 py-2.5 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-colors w-full sm:w-auto text-center">
                        Batal
                      </a>
                      <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors w-full sm:w-auto flex items-center justify-center gap-2 shadow-sm shadow-blue-600/30">
                        Simpan Perubahan
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

<?php 

include '../layout/footer.php'; 
?>
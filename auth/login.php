<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../config.php'; 

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Asumsi nama tabel adalah 'users'

        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username AND password = :password");
       

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $user = $stmt->fetch();
        var_dump($user);
        // Pengecekan password
        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Cek role user untuk diarahkan ke halaman yang sesuai
            if ($username === 'admin') {
                header("Location: ../dashboard/admin/index.php");
            } else {
                header("Location: ../dashboard/index.php");
            }
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="en" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
  <head>
    <title>Login | Träffa Coffee</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Login to Träffa Coffee" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    
    <!-- [Font] Family -->
    <link rel="stylesheet" href="../assets/fonts/inter/inter.css" id="main-font-link" />
    <link rel="stylesheet" href="../assets/fonts/tabler-icons.min.css" />
    <link rel="stylesheet" href="../assets/fonts/feather.css" />
    <link rel="stylesheet" href="../assets/fonts/fontawesome.css" />
    <link rel="stylesheet" href="../assets/fonts/material.css" />
    
    <!-- Tailwind CSS (Menggantikan style.css kustom) -->
    <script src="https://cdn.tailwindcss.com"></script>
  </head>

  <body class="bg-gray-50 text-gray-800">
    <!-- [ Pre-loader ] start -->
    <!-- <div class="fixed inset-0 z-[1034] bg-white dark:bg-gray-900 transition-opacity duration-300 loader-bg">
      <div class="absolute top-0 inline-block w-full h-[5px] overflow-hidden bg-blue-100 loader-track">
        <div class="absolute left-0 top-0 h-[5px] w-[300px] bg-blue-500 animate-pulse loader-fill"></div>
      </div>
    </div> -->
    <!-- [ Pre-loader ] End -->

    <div class="relative w-full">
      <div class="flex min-h-screen w-full items-center justify-center bg-cover bg-center bg-no-repeat p-6 relative bg-[url('../images/authentication/img-auth-bg.jpg')]">
        
        <!-- Login Card -->
        <div class="relative w-full max-w-[480px] rounded-2xl border border-gray-200 bg-white shadow-lg sm:my-12">
          <div class="p-8 sm:p-10">
            
            <div class="my-6 text-center">
              <a href="#" class="block w-full">
                <img src="../assets/image/logo.svg" alt="Logo Trafa Coffee" class="mx-auto h-auto w-[120px]" />
              </a>
            </div>
            
            <h4 class="my-4 text-center text-xl font-semibold text-gray-800 dark:text-gray-900">Login Akun Anda</h4>
            
            <?php if(isset($error)): ?>
              <div class="mb-4 rounded-lg bg-red-50 p-3 text-center text-sm text-red-600 border border-red-100">
                <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="" method="POST">
              <div class="mb-4">
                <input type="text" name="username" id="username" placeholder="Username" required 
                       class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition-all duration-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-700" />
              </div>
              <div class="mb-5">
                <input type="password" name="password" id="password" placeholder="Password" required 
                       class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition-all duration-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:text-gray-700" />
              </div>
              <div class="mb-6 flex flex-wrap items-center justify-between">
                <div class="flex items-center gap-2">
                  <input type="checkbox" id="customCheckc1" checked 
                         class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 transition-all focus:ring-2 focus:ring-blue-500" />
                  <label for="customCheckc1" class="cursor-pointer text-sm text-gray-500 dark:text-gray-400">Remember me?</label>
                </div>
              </div>
              <div class="mt-4">
                <button type="submit" name="login" 
                        class="inline-block w-full rounded-full bg-blue-600 px-5 py-3 text-center text-base font-medium text-white shadow-sm transition-all duration-200 ease-in-out hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800">
                  Login
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>

    <!-- Required Js -->
    <script src="../assets/js/plugins/simplebar.min.js"></script>
    <script src="../assets/js/plugins/popper.min.js"></script>
    <script src="../assets/js/icon/custom-font.js"></script>
    <script src="../assets/js/plugins/feather.min.js"></script>
    <script src="../assets/js/component.js"></script>
    <script src="../assets/js/theme.js"></script>
    <script src="../assets/js/script.js"></script>
  </body>
</html>
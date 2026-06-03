<?php
session_start();
require_once '../config.php'; 
require_once '../path.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 1) {
        header("Location: " . BASE_URL . "kasir"); 
        exit;
    } 
    elseif ($_SESSION['role'] == 2) {
        header("Location: " . BASE_URL . "admin");
        exit;
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $user = $stmt->fetch();

        if ($user && $user['password'] === $password) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            if ($user['role'] === 2) {
                header("Location: " . BASE_URL . "admin"); 
            } else {
                header("Location: " . BASE_URL . "kasir");
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Login to Träffa Coffee" />
    <link rel="icon" href="<?= BASE_URL; ?>assets/image/favicon.svg" type="image/x-icon" />
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  </head> 

  <body class="bg-gray-50 text-gray-800">

    <div class="relative w-full">
      <div class="flex min-h-screen w-full items-center justify-center bg-cover bg-center bg-no-repeat p-6 relative bg-[url('../images/authentication/img-auth-bg.jpg')]">
        
        <!-- Login Card -->
        <div class="relative w-full max-w-[480px] rounded-2xl border border-gray-200 bg-white shadow-lg sm:my-12">
          <div class="p-8 sm:p-10">
            
            <div class="my-6 text-center">
              <a href="#" class="block w-full">
                <img src="<?= BASE_URL; ?>assets/image/logo.svg" alt="Logo Trafa Coffee" class="mx-auto h-auto w-[120px]" />
              </a>
            </div>
            
            <h4 class="my-4 text-center text-xl font-semibold text-gray-800">Login Akun Anda</h4>
            
            <?php if(isset($error)): ?>
              <div class="mb-4 rounded-lg bg-red-50 p-3 text-center text-sm text-red-600 border border-red-100">
                <?php echo $error; ?>
              </div>
            <?php endif; ?>

            <form action="" method="POST">
              <div class="mb-4">
                <input type="text" name="username" id="username" placeholder="Username" required 
                       class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition-all duration-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
              </div>

              <!-- Password + show/hide -->
              <div class="mb-5 relative">
                <input type="password" name="password" id="password" placeholder="Password" required 
                       class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 pr-11 text-sm text-gray-700 outline-none transition-all duration-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                    <i id="eye-icon" class="fa-solid fa-eye text-sm"></i>
                </button>
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

    <script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
    </script>

  </body>
</html>
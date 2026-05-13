<nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-300 ease-in-out max-lg:-left-[280px] pc-sidebar">
    <div class="h-full w-full flex flex-col">
        <div class="flex h-[74px] items-center px-6 py-4">
            <a href="<?= BASE_URL; ?>index" class="flex items-center gap-3">
                <img src="<?= BASE_URL; ?>assets/image/icon.png" class="h-8 w-8" alt="logo" />
                <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Kasir Panel</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto py-3">
            <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                    <div class="ml-3 overflow-hidden">
                        <h6 class="truncate text-sm font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['username'] ?? 'Kasir'); ?></h6>
                        <small class="text-xs text-gray-500">Kasir</small>
                    </div>
                </div>
            </div>

            <ul class="flex flex-col gap-1.5 px-4 py-2 text-sm">
                <li>
                    <a href="<?=  BASE_URL; ?>kasir/index" 
                       class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                       <?= ($currentPage == 'dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                        <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span class="font-medium">Dashboard</span>
                    </a>
                </li>
                
                <li>
                    <a href="<?= BASE_URL; ?>kasir/history/order" 
                       class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                       <?= ($currentPage == 'riwayat') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                        <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'riwayat') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </span>
                        <span class="font-medium">Riwayat Pesanan</span>
                    </a>
                </li>

                <li class="mt-5 px-4 py-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Authentication</span>
                </li>

                <li>
                    <a href="<?= BASE_URL; ?>kasir/profile/index" 
                       class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                       <?= ($currentPage == 'akun') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                        <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'akun') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <span class="font-medium">Tentang Akun</span>
                    </a>
                </li>

                <li>
                    <a href="<?= BASE_URL; ?>auth/logout" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                        <span class="flex w-6 justify-center text-lg text-gray-400 group-hover:text-red-600"><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="font-medium">Log Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<header class="fixed inset-x-0 top-0 z-[1024] flex h-[74px] items-center bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all duration-300 lg:left-[280px] pc-header">
    <div class="flex grow items-center sm:px-2">
        <div class="mr-auto">
            <button class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="sidebar-toggle-btn">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
        </div>
    </div>
</header>
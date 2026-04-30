<nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-300 ease-in-out max-lg:-left-[280px] pc-sidebar">
    <div class="h-full w-full flex flex-col">
        <div class="flex h-[74px] items-center px-6 py-4">
            <a href="<?= BASE_URL; ?>index" class="flex items-center gap-3">
                <img src="<?= BASE_URL; ?>assets/image/logo.svg" class="h-8 w-8" alt="logo" />
                <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Admin Panel</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto py-3">
            <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="flex items-center">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm">
                        <?= htmlspecialchars($initials); ?>
                    </div>
                    <div class="ml-3 mr-2 grow">
                        <h6 class="mb-0 text-sm font-semibold text-gray-800"><?=  htmlspecialchars($_SESSION['name']); ?></h6>
                        <small class="text-xs text-gray-500">Administrator</small>
                    </div>
                </div>
            </div>
            <ul class="flex flex-col gap-1.5 px-4 py-2">
        
              <li>
                <a href="<?=  BASE_URL; ?>dashboard/admin/index" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'dashboard') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <i class="fa-solid fa-house"></i>
                    </span>
                    <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="<?=  BASE_URL; ?>dashboard/admin/laporan/index" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'laporan') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'laporan') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <<i class="fa-solid fa-file-invoice-dollar"></i>
                    </span>
                    <span class="font-medium">Laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="<?=  BASE_URL; ?>dashboard/admin/menu/index" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'menu') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'menu') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <i class="fa-solid fa-mug-hot"></i>
                    </span>
                    <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>

              <li>
                <a href="<?=  BASE_URL; ?>dashboard/admin/meja/index" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'meja') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'meja') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <i class="fa-solid fa-chair"></i>
                    </span>
                    <span class="font-medium">Manajemen Meja</span>
                </a>
              </li>

              <li>
                <a href="<?= BASE_URL; ?>dashboard/admin/kasir/index" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'kasir') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'kasir') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <i class="fa-solid fa-users-gear"></i>
                    </span>
                        <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="<?=  BASE_URL; ?>auth/logout" 
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 transition-all duration-200 
                    <?= ($currentPage == 'logout') ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600'; ?>">
                    <span class="flex w-6 justify-center text-lg <?= ($currentPage == 'logout') ? 'text-white' : 'text-gray-400 group-hover:text-blue-600'; ?>">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </span>
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
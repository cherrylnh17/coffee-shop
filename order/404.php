<?php
$title = "Halaman Tidak Ditemukan";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-md mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <!-- Header minimal -->
    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-3 px-4 rounded-b-2xl">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()"
                class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-colors">
                <i class="ph-bold ph-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Träffa Coffee & Eatery</h1>
                <p class="text-xs text-gray-500">Halaman tidak ditemukan</p>
            </div>
        </div>
    </header>

    <!-- Konten 404 -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-16 text-center">

        <!-- Ilustrasi -->
        <div class="relative mb-8 select-none">
            <!-- Lingkaran dekoratif -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-52 h-52 rounded-full bg-sky-50 border-2 border-sky-100 animate-pulse-slow"></div>
            </div>
            <div class="relative z-10 flex flex-col items-center justify-center w-52 h-52">
                <!-- Ikon cangkir kopi -->
                <i class="ph ph-coffee text-7xl text-sky-300 mb-1"></i>
                <!-- Angka 404 -->
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-5xl font-black text-sky-500 tracking-tight leading-none">4</span>
                    <i class="ph-fill ph-smiley-melting text-5xl text-sky-400 leading-none"></i>
                    <span class="text-5xl font-black text-sky-500 tracking-tight leading-none">4</span>
                </div>
            </div>
        </div>

        <!-- Teks utama -->
        <h2 class="text-2xl font-black text-gray-900 mb-2 leading-tight">
            Aduh, halaman ini<br>tidak ada nih! ☕
        </h2>
        <p class="text-sm text-gray-500 leading-relaxed max-w-xs mb-8">
            Sepertinya menu yang kamu cari sudah habis atau alamatnya salah.
            Yuk balik ke beranda dan pesan yang lain!
        </p>

        <!-- Tombol aksi -->
        <div class="w-full max-w-xs space-y-3">
            <!-- Tombol utama: kembali ke beranda -->
            <a href="javascript:history.back()"
                class="flex items-center justify-center gap-2 w-full bg-sky-500 hover:bg-sky-600 active:scale-95 text-white font-bold py-3.5 rounded-2xl shadow-md transition-all">
                <i class="ph-bold ph-arrow-left text-lg"></i>
                Kembali ke Halaman Sebelumnya
            </a>

        </div>
    </div>

    <!-- Footer dekoratif -->
    <div class="pb-10 pt-4 flex flex-col items-center gap-1">
        <div class="flex items-center gap-1.5 text-gray-300 text-xs">
            <i class="ph-fill ph-coffee text-sm"></i>
            <span>Error 404 &mdash; Träffa Coffee & Eatery</span>
        </div>
    </div>

</main>

<?php include __DIR__ . '/layout/footer.php'; ?>
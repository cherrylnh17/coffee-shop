<?php 
  $title = "Pilih Meja"; 
  include 'layout/header.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="w-full max-w-md mx-auto bg-gray-50 min-h-screen relative flex flex-col justify-center px-4 overflow-hidden shadow-2xl">
    
    <div class="absolute top-0 left-0 w-full h-80 bg-sky-500 rounded-b-[50px] z-0"></div>

    <div class="relative z-10 w-full">
        <div class="text-center mb-6 text-white">
            <h1 class="text-3xl font-extrabold mb-1">Träffa Coffee</h1>
            <p class="text-sky-100 text-sm">Selamat datang! Silakan pilih meja Anda.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] p-8 text-center border border-gray-100">
            <div class="w-20 h-20 mx-auto bg-sky-50 rounded-full flex items-center justify-center mb-5 shadow-inner">
                <i class="ph ph-armchair text-4xl text-sky-500"></i>
            </div>
            
            <h2 class="text-xl font-bold text-gray-800 mb-2">Nomor Meja</h2>
            <p class="text-sm text-gray-500 mb-6">Masukkan nomor meja tempat Anda duduk untuk mulai memesan menu.</p>

            <div class="flex items-center justify-center gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="ph ph-hash text-gray-400 text-xl"></i>
                    </div>
                    <input type="text" id="input-meja" 
                           class="w-full bg-gray-50 border-2 border-gray-200 text-gray-900 text-2xl font-black rounded-xl focus:ring-sky-500 focus:border-sky-500 block py-4 pl-12 pr-4 transition-colors text-center uppercase" 
                           placeholder="00" autocomplete="off">
                </div>
            </div>
            
            <button id="btn-lanjut" class="w-full mt-5 bg-sky-500 hover:bg-sky-600 text-white font-bold py-4 px-5 rounded-xl transition-all active:scale-95 shadow-lg shadow-sky-500/30 flex items-center justify-center gap-2 text-lg">
                <span>Lanjutkan</span>
                <i class="ph ph-arrow-right font-bold"></i>
            </button>
        </div>
    </div>
</main>

<script>
function prosesMeja() {
    let inputEl = document.getElementById('input-meja');
    let tableName = inputEl.value.trim().toUpperCase();
    
    // Jika input kosong
    if (tableName === '') {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Nomor meja tidak boleh kosong!',
            confirmButtonColor: '#0ea5e9'
        });
        return;
    }

    let btnLanjut = document.getElementById('btn-lanjut');
    let originalBtnContent = btnLanjut.innerHTML;
    
    // Ubah tombol jadi loading
    btnLanjut.innerHTML = '<i class="ph ph-spinner animate-spin text-2xl"></i> Memeriksa...';
    btnLanjut.disabled = true;

    // Proses pengecekan ke database (AJAX)
    fetch('server/check_table', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'table_name=' + encodeURIComponent(tableName)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.status) {
            btnLanjut.innerHTML = originalBtnContent;
            btnLanjut.disabled = false;
            
            Swal.fire({
                icon: 'error',
                title: 'Meja Tidak Valid!',
                text: 'Nomor meja tidak ditemukan. Silakan periksa kembali nomor di meja Anda.',
                confirmButtonColor: '#0ea5e9'
            }).then(() => {
                inputEl.value = ''; // Kosongkan input
                inputEl.focus();    // Fokuskan kembali kursor ke input
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Mengalihkan ke menu...',
                timer: 1000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'index?code=' + encodeURIComponent(tableName);
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btnLanjut.innerHTML = originalBtnContent;
        btnLanjut.disabled = false;
        
        Swal.fire({
            icon: 'error',
            title: 'Koneksi Bermasalah',
            text: 'Gagal menghubungi server. Periksa koneksi internet Anda.'
        });
    });
}

// Trigger saat tombol di klik
document.getElementById('btn-lanjut').addEventListener('click', prosesMeja);

// Trigger saat user menekan enter di keyboard
document.getElementById('input-meja').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        prosesMeja();
    }
});
</script>

<?php include 'layout/footer.php'; ?>
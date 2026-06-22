<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// Buat tabel jika belum ada
$pdo->exec("CREATE TABLE IF NOT EXISTS `fee_setting` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `type` tinyint UNSIGNED NOT NULL COMMENT '1 Untuk Persen, 2 Untuk Fix',
    `value` decimal(10,2) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`)
)");

$stmt = $pdo->query("SELECT * FROM fee_setting ORDER BY id DESC");
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Manajemen Biaya & Pajak";
$currentPage = "tax";
?>

<?php
include __DIR__ . '/../layout/header.php';
include __DIR__ . '/../layout/sidebar.php';
?>

<main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
  <div class="p-4 sm:p-6 lg:p-8">

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'success'): ?>
            <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                <div><span class="font-medium">Berhasil!</span> <?= htmlspecialchars($_GET['msg']) ?></div>
                <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-green-800 hover:text-green-900"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php elseif ($_GET['status'] == 'error'): ?>
            <div id="alert-message" class="mb-4 flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <div><span class="font-medium">Gagal!</span> <?= htmlspecialchars($_GET['msg']) ?></div>
                <button type="button" onclick="document.getElementById('alert-message').remove()" class="text-red-800 hover:text-red-900"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>
        <script>
            setTimeout(() => {
                const alertBox = document.getElementById('alert-message');
                if (alertBox) { alertBox.style.opacity = '0'; setTimeout(() => alertBox.remove(), 500); }
            }, 4000);
        </script>
    <?php endif; ?>

    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h3 class="text-xl font-bold text-gray-800 whitespace-nowrap">Daftar Biaya & Pajak</h3>
            <button data-modal-target="tambah-fee-modal" data-modal-toggle="tambah-fee-modal"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 whitespace-nowrap" type="button">
                <i class="fa-solid fa-plus mr-2"></i>Tambah Biaya
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-xs uppercase text-gray-700 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">Nama Biaya</th>
                        <th class="w-32 px-6 py-4 text-center">Tipe</th>
                        <th class="w-36 px-6 py-4 text-center">Nilai</th>
                        <th class="w-32 px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($fees)): ?>
                        <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">Data biaya belum tersedia.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($fees as $fee): ?>
                        <tr class="border-b border-gray-100 bg-white hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-gray-900"><?= htmlspecialchars($fee['name']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($fee['type'] == 1): ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 border border-blue-100">
                                        <i class="fa-solid fa-percent text-[10px]"></i> Persen
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 border border-emerald-100">
                                        <i class="fa-solid fa-money-bill text-[10px]"></i> Fixed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600">
                                <?php if ($fee['type'] == 1): ?>
                                    <?= rtrim(rtrim(number_format((float)$fee['value'], 2, '.', ''), '0'), '.') ?> %
                                <?php else: ?>
                                    Rp <?= number_format((float)$fee['value'], 0, ',', '.') ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        onclick="openEditModal(<?= $fee['id'] ?>, '<?= htmlspecialchars(addslashes($fee['name'])) ?>', <?= (int)$fee['type'] ?>, <?= (float)$fee['value'] ?>)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded bg-blue-50 text-blue-600 hover:bg-blue-100">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>admin/tax/hapus?id=<?= $fee['id'] ?>"
                                       onclick="return confirm('Yakin ingin menghapus \'<?= htmlspecialchars(addslashes($fee['name'])) ?>\'?')"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded bg-red-50 text-red-600 hover:bg-red-100">
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

        <div class="mt-4 text-sm text-gray-500">
            Total: <span class="font-bold text-gray-900"><?= count($fees) ?> Data</span>
        </div>
    </div>
  </div>
</main>

<!-- Modal Tambah -->
<div id="tambah-fee-modal" tabindex="-1" aria-hidden="true"
    class="fixed left-0 right-0 top-0 z-[9999] hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-md">
    <div class="relative max-h-full w-full max-w-md p-4">
        <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Biaya / Pajak</h3>
                <button type="button" data-modal-hide="tambah-fee-modal"
                    class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="<?= BASE_URL ?>admin/tax/tambah" method="POST" class="p-4 md:p-5">

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Nama Biaya</label>
                    <input type="text" name="name"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                        placeholder="Contoh: PPN, Service Charge" required />
                </div>

                <!-- Toggle tipe -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Tipe Biaya</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="1" class="peer hidden" checked onchange="toggleValueInput('tambah', 1)" />
                            <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-sm font-semibold text-gray-600 transition-all
                                peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-blue-200">
                                <i class="fa-solid fa-percent"></i> Persen
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" value="2" class="peer hidden" onchange="toggleValueInput('tambah', 2)" />
                            <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-sm font-semibold text-gray-600 transition-all
                                peer-checked:border-emerald-400 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:ring-2 peer-checked:ring-emerald-100 hover:border-emerald-200">
                                <i class="fa-solid fa-money-bill"></i> Fixed (Rp)
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Input nilai persen -->
                <div id="tambah-input-persen" class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Besaran (%)</label>
                    <div class="relative">
                        <input type="number" name="value" id="tambah-value-persen"
                            step="0.01" min="0.01" max="100"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                            placeholder="Contoh: 12 atau 1.5" required />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">%</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Maksimal 100%, boleh menggunakan koma (contoh: 1.5%)</p>
                    <p id="tambah-error-persen" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <!-- Input nilai fixed (tersembunyi awalnya) -->
                <div id="tambah-input-fixed" class="mb-6 hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Nominal (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">Rp</span>
                        <input type="number" name="value_fixed" id="tambah-value-fixed"
                            step="1" min="0"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            placeholder="Contoh: 5000" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Nominal tetap dalam Rupiah yang ditambahkan ke total tagihan</p>
                    <p id="tambah-error-fixed" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <button type="submit" id="tambah-submit" disabled
                    class="inline-flex w-full justify-center rounded-lg bg-gray-300 text-gray-400 cursor-not-allowed px-4 py-2.5 text-sm font-medium transition-all duration-200">
                    <i class="fa-solid fa-plus mr-2 mt-0.5"></i> Simpan
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<button id="trigger-edit-modal" data-modal-target="edit-fee-modal" data-modal-toggle="edit-fee-modal" class="hidden"></button>
<div id="edit-fee-modal" tabindex="-1" aria-hidden="true"
    class="fixed left-0 right-0 top-0 z-[9999] hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-md">
    <div class="relative max-h-full w-full max-w-md p-4">
        <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                <h3 class="text-lg font-semibold text-gray-900">Edit Biaya / Pajak</h3>
                <button type="button" data-modal-hide="edit-fee-modal"
                    class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-900">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="<?= BASE_URL ?>admin/tax/edit" method="POST" class="p-4 md:p-5">
                <input type="hidden" name="id" id="edit_id">

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Nama Biaya</label>
                    <input type="text" name="name" id="edit_name"
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                        required />
                </div>

                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Tipe Biaya</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" id="edit_type_1" value="1" class="peer hidden" onchange="toggleValueInput('edit', 1)" />
                            <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-sm font-semibold text-gray-600 transition-all
                                peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:text-blue-700 peer-checked:ring-2 peer-checked:ring-blue-100 hover:border-blue-200">
                                <i class="fa-solid fa-percent"></i> Persen
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="type" id="edit_type_2" value="2" class="peer hidden" onchange="toggleValueInput('edit', 2)" />
                            <div class="flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-gray-50 p-3 text-sm font-semibold text-gray-600 transition-all
                                peer-checked:border-emerald-400 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:ring-2 peer-checked:ring-emerald-100 hover:border-emerald-200">
                                <i class="fa-solid fa-money-bill"></i> Fixed (Rp)
                            </div>
                        </label>
                    </div>
                </div>

                <div id="edit-input-persen" class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Besaran (%)</label>
                    <div class="relative">
                        <input type="number" name="value" id="edit_value_persen"
                            step="0.01" min="0.01" max="100"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 pr-10 text-sm text-gray-900 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
                            required />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">%</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Maksimal 100%, boleh menggunakan koma (contoh: 1.5%)</p>
                </div>

                <div id="edit-input-fixed" class="mb-6 hidden">
                    <label class="mb-2 block text-sm font-medium text-gray-900">Nominal (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">Rp</span>
                        <input type="number" name="value_fixed" id="edit_value_fixed"
                            step="1" min="0"
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-900 outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500" />
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Nominal tetap dalam Rupiah yang ditambahkan ke total tagihan</p>
                </div>

                <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                    </button>
                    <button data-modal-hide="edit-fee-modal" type="button"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../layout/footer.php'; 
?>

<script>
    // ── Validasi realtime & toggle disabled button ──────────────────────────

    function validateTambah() {
        const btn      = document.getElementById('tambah-submit');
        const nameVal  = document.querySelector('#tambah-fee-modal input[name="name"]').value.trim();
        const type     = document.querySelector('#tambah-fee-modal input[name="type"]:checked').value;

        const persenInput = document.getElementById('tambah-value-persen');
        const fixedInput  = document.getElementById('tambah-value-fixed');
        const errPersen   = document.getElementById('tambah-error-persen');
        const errFixed    = document.getElementById('tambah-error-fixed');

        let valueOk = false;

        if (type == 1) {
            const val = parseFloat(persenInput.value);
            if (persenInput.value === '' || isNaN(val)) {
                errPersen.textContent = 'Nilai persentase wajib diisi.';
                errPersen.classList.remove('hidden');
                persenInput.classList.add('border-red-400');
            } else if (val <= 0) {
                errPersen.textContent = 'Persentase harus lebih dari 0%.';
                errPersen.classList.remove('hidden');
                persenInput.classList.add('border-red-400');
            } else if (val > 100) {
                errPersen.textContent = 'Persentase tidak boleh melebihi 100%.';
                errPersen.classList.remove('hidden');
                persenInput.classList.add('border-red-400');
            } else {
                errPersen.classList.add('hidden');
                persenInput.classList.remove('border-red-400');
                valueOk = true;
            }
        } else {
            const val = parseFloat(fixedInput.value);
            if (fixedInput.value === '' || isNaN(val)) {
                errFixed.textContent = 'Nominal wajib diisi.';
                errFixed.classList.remove('hidden');
                fixedInput.classList.add('border-red-400');
            } else if (val < 0) {
                errFixed.textContent = 'Nominal tidak boleh negatif.';
                errFixed.classList.remove('hidden');
                fixedInput.classList.add('border-red-400');
            } else if (val === 0) {
                errFixed.textContent = 'Nominal harus lebih dari Rp 0.';
                errFixed.classList.remove('hidden');
                fixedInput.classList.add('border-red-400');
            } else {
                errFixed.classList.add('hidden');
                fixedInput.classList.remove('border-red-400');
                valueOk = true;
            }
        }

        const isValid = nameVal !== '' && valueOk;

        btn.disabled = !isValid;
        if (isValid) {
            const isPersen = type == 1;
            btn.className = `inline-flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition-all duration-200 cursor-pointer ${isPersen ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700'}`;
        } else {
            btn.className = 'inline-flex w-full justify-center rounded-lg bg-gray-300 text-gray-400 cursor-not-allowed px-4 py-2.5 text-sm font-medium transition-all duration-200';
        }
    }

    // Pasang listener ke semua input di form tambah
    document.addEventListener('DOMContentLoaded', () => {
        const tambahForm = document.querySelector('#tambah-fee-modal form');

        tambahForm.querySelector('input[name="name"]').addEventListener('input', validateTambah);
        document.getElementById('tambah-value-persen').addEventListener('input', validateTambah);
        document.getElementById('tambah-value-fixed').addEventListener('input', validateTambah);
        tambahForm.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', () => {
            // Reset error & value lawan saat ganti tipe
            document.getElementById('tambah-error-persen').classList.add('hidden');
            document.getElementById('tambah-error-fixed').classList.add('hidden');
            document.getElementById('tambah-value-persen').classList.remove('border-red-400');
            document.getElementById('tambah-value-fixed').classList.remove('border-red-400');
            validateTambah();
        }));

        // Jalankan sekali saat load supaya tombol langsung disabled
        validateTambah();
    });

    // ── Toggle tampilan input persen / fixed ────────────────────────────────

    // Toggle tampilan input persen / fixed
    function toggleValueInput(prefix, type) {
        const persenEl = document.getElementById(prefix + '-input-persen');
        const fixedEl  = document.getElementById(prefix + '-input-fixed');
        const persenInput = document.getElementById(prefix === 'tambah' ? 'tambah-value-persen' : 'edit_value_persen');
        const fixedInput  = document.getElementById(prefix === 'tambah' ? 'tambah-value-fixed'  : 'edit_value_fixed');
        const submitBtn   = document.getElementById(prefix === 'tambah' ? 'tambah-submit' : null);

        if (type == 1) {
            persenEl.classList.remove('hidden');
            fixedEl.classList.add('hidden');
            persenInput.required = true;
            fixedInput.required  = false;
            if (submitBtn) submitBtn.classList.replace('bg-emerald-600', 'bg-blue-600'), submitBtn.classList.replace('hover:bg-emerald-700', 'hover:bg-blue-700');
        } else {
            persenEl.classList.add('hidden');
            fixedEl.classList.remove('hidden');
            persenInput.required = false;
            fixedInput.required  = true;
            if (submitBtn) submitBtn.classList.replace('bg-blue-600', 'bg-emerald-600'), submitBtn.classList.replace('hover:bg-blue-700', 'hover:bg-emerald-700');
        }
    }

    // Buka modal edit & isi data
    function openEditModal(id, name, type, value) {
        document.getElementById('edit_id').value    = id;
        document.getElementById('edit_name').value  = name;

        if (type == 1) {
            document.getElementById('edit_type_1').checked   = true;
            document.getElementById('edit_value_persen').value = value;
            toggleValueInput('edit', 1);
        } else {
            document.getElementById('edit_type_2').checked  = true;
            document.getElementById('edit_value_fixed').value = value;
            toggleValueInput('edit', 2);
        }

        document.getElementById('trigger-edit-modal').click();
    }

    // Intersep submit form tambah: pindahkan value dari field aktif ke name="value"
    document.querySelector('#tambah-fee-modal form').addEventListener('submit', function(e) {
        const type = this.querySelector('input[name="type"]:checked').value;
        const persenInput = document.getElementById('tambah-value-persen');
        const fixedInput  = document.getElementById('tambah-value-fixed');

        if (type == 2) {
            persenInput.name = '_value_disabled';
            fixedInput.name  = 'value';
        } else {
            persenInput.name = 'value';
            fixedInput.name  = '_value_disabled';
        }
    });

    // Intersep submit form edit
    document.querySelector('#edit-fee-modal form').addEventListener('submit', function(e) {
        const type = this.querySelector('input[name="type"]:checked').value;
        const persenInput = document.getElementById('edit_value_persen');
        const fixedInput  = document.getElementById('edit_value_fixed');

        if (type == 2) {
            persenInput.name = '_value_disabled';
            fixedInput.name  = 'value';
        } else {
            persenInput.name = 'value';
            fixedInput.name  = '_value_disabled';
        }
    });

    // Sidebar
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('main');

        document.getElementById('sidebar-hide')?.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-[280px]');
            header.classList.toggle('lg:left-0');
            mainContent.classList.toggle('lg:ml-[280px]');
            mainContent.classList.toggle('lg:ml-0');
        });

        document.getElementById('mobile-collapse')?.addEventListener('click', (e) => {
            e.preventDefault();
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
        });
    });
</script>
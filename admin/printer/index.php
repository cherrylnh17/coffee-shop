<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// Fetch all printers
try {
    $printers = $pdo->query("SELECT * FROM printer ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $printers = [];
}

$type_labels = [1 => 'Bluetooth', 2 => 'Network', 3 => 'USB'];
$type_colors = [
    1 => 'bg-blue-50 text-blue-700 border-blue-200',
    2 => 'bg-green-50 text-green-700 border-green-200',
    3 => 'bg-orange-50 text-orange-700 border-orange-200',
];
$type_icons = [
    1 => 'fa-solid fa-bluetooth',
    2 => 'fa-solid fa-network-wired',
    3 => 'fa-solid fa-plug',
];

// Flash message dari redirect
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$pageTitle   = "Manajemen Printer";
$currentPage = "printer";

include '../layout/header.php';
include '../layout/sidebar.php';
?>

<main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
  <div class="p-4 sm:p-6 lg:p-8">

    <!-- Flash message -->
    <?php if ($flash): ?>
    <div id="flash-msg" class="mb-4 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium border
        <?= $flash['type'] === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' ?>">
        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
        <?= htmlspecialchars($flash['msg']) ?>
        <button onclick="this.parentElement.remove()" class="ml-auto text-inherit opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <?php endif; ?>

    <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100">

      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
        <div>
          <h3 class="font-bold text-xl text-gray-800">Manajemen Printer</h3>
          <p class="text-sm text-gray-500 mt-0.5">Kelola printer Bluetooth, Network, dan USB</p>
        </div>
        <button onclick="openTypePickerModal()"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700 shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Printer
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full text-sm text-left whitespace-nowrap">
          <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 uppercase text-[11px] tracking-wider">
            <tr>
              <th class="px-5 py-4 w-12 text-center">No</th>
              <th class="px-5 py-4">Nama</th>
              <th class="px-5 py-4">Tipe</th>
              <th class="px-5 py-4">Koneksi</th>
              <th class="px-5 py-4">Timeout</th>
              <th class="px-5 py-4 text-center">Status</th>
              <th class="px-5 py-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <?php if (empty($printers)): ?>
              <tr>
                <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                  <i class="fa-solid fa-print text-4xl mb-3 block opacity-30"></i>
                  Belum ada printer yang terdaftar.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($printers as $i => $p): ?>
              <tr class="hover:bg-blue-50/40 transition-colors group">
                <td class="px-5 py-4 text-center font-medium text-gray-400"><?= $i + 1 ?></td>
                <td class="px-5 py-4">
                  <div class="font-bold text-gray-900"><?= htmlspecialchars($p['name']) ?></div>
                </td>
                <td class="px-5 py-4">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border <?= $type_colors[$p['type']] ?? 'bg-gray-50 text-gray-600 border-gray-200' ?>">
                    <i class="<?= $type_icons[$p['type']] ?? 'fa-solid fa-print' ?> text-[10px]"></i>
                    <?= $type_labels[$p['type']] ?? '-' ?>
                  </span>
                </td>
                <td class="px-5 py-4 text-gray-600 font-mono text-xs">
                  <?php if ($p['type'] == 1): ?>
                    <div><span class="text-gray-400">MAC:</span> <?= htmlspecialchars($p['bt_mac'] ?: '-') ?></div>
                    <div><span class="text-gray-400">Dev:</span> <?= htmlspecialchars($p['rfcomm_dev'] ?: '-') ?></div>
                  <?php elseif ($p['type'] == 2): ?>
                    <div><span class="text-gray-400">IP:</span> <?= htmlspecialchars($p['ip_address'] ?: '-') ?>:<?= htmlspecialchars($p['port'] ?: '-') ?></div>
                  <?php elseif ($p['type'] == 3): ?>
                    <div><span class="text-gray-400">Dev:</span> <?= htmlspecialchars($p['usb_device'] ?: '-') ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-gray-600">
                  <?= ($p['timeout'] && $p['type'] == 1) ? htmlspecialchars($p['timeout']) . 's' : '<span class="text-gray-300">—</span>' ?>
                </td>
                <td class="px-5 py-4 text-center">
                  <?php if ($p['is_active']): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Aktif
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                      <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Nonaktif
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-50 text-gray-600 hover:bg-blue-600 hover:text-white border border-gray-200 hover:border-blue-600 transition-all shadow-sm">
                        <i class="fa-solid fa-pen-to-square text-[10px]"></i> Edit
                    </button>
                    <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-50 text-red-500 hover:bg-red-600 hover:text-white border border-gray-200 hover:border-red-600 transition-all shadow-sm">
                        <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                    </button>
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

<!--  MODAL 1: Pilih Tipe  -->
<div id="modal-type-picker" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
  <div class="relative w-full max-w-sm p-4">
    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
      <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-print text-blue-600"></i> Pilih Tipe Printer
        </h3>
        <button onclick="closeTypePickerModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-900">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
      <div class="p-5 space-y-3">
        <p class="text-sm text-gray-500 mb-4">Pilih jenis koneksi printer yang akan ditambahkan.</p>
        <button onclick="openAddModal(1)" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-blue-400 hover:bg-blue-50 transition-all group text-left">
          <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
            <i class="fa-solid fa-bluetooth text-blue-600 text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-gray-800 text-sm">Bluetooth</div>
            <div class="text-xs text-gray-400">Koneksi via RFCOMM / MAC Address</div>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-300 ml-auto group-hover:text-blue-400 transition-colors"></i>
        </button>
        <button onclick="openAddModal(2)" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-green-400 hover:bg-green-50 transition-all group text-left">
          <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
            <i class="fa-solid fa-network-wired text-green-600 text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-gray-800 text-sm">Network / LAN</div>
            <div class="text-xs text-gray-400">Koneksi via IP Address & Port</div>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-300 ml-auto group-hover:text-green-400 transition-colors"></i>
        </button>
        <button onclick="openAddModal(3)" class="w-full flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-orange-400 hover:bg-orange-50 transition-all group text-left">
          <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center group-hover:bg-orange-200 transition-colors">
            <i class="fa-solid fa-plug text-orange-600 text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-gray-800 text-sm">USB</div>
            <div class="text-xs text-gray-400">Koneksi via device path (mis. /dev/usb/lp0)</div>
          </div>
          <i class="fa-solid fa-chevron-right text-gray-300 ml-auto group-hover:text-orange-400 transition-colors"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!--  MODAL 2: Form Tambah / Edit Printer  -->
<div id="modal-form" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
  <div class="relative w-full max-w-lg p-4 max-h-screen overflow-y-auto">
    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">

      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
        <h3 id="modal-title" class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <i id="modal-icon" class="fa-solid fa-print text-blue-600"></i>
          <span id="modal-title-text">Tambah Printer</span>
        </h3>
        <button onclick="closeFormModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-900">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>

      <!-- Tipe badge -->
      <div class="px-5 pt-4">
        <div id="type-badge" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border"></div>
      </div>

      <!-- Form -->
      <form id="printer-form" method="POST" action="<?= BASE_URL ?>admin/printer/proses">
        <input type="hidden" name="action" id="form-action" value="tambah">
        <input type="hidden" name="id"     id="form-id"     value="">
        <input type="hidden" name="type"   id="form-type"   value="">

        <div class="p-5 space-y-4">

          <!-- Nama (semua tipe) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
              Nama Printer <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="f-name" required placeholder="Contoh: Printer Kasir 1"
              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
          </div>

          <!--  BLUETOOTH fields  -->
          <div id="fields-bluetooth" class="space-y-4 hidden">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                MAC Address <span class="text-red-500">*</span>
              </label>
              <input type="text" name="bt_mac" id="f-bt-mac" placeholder="Contoh: 00:11:22:33:44:55"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
              <p class="text-xs text-gray-400 mt-1">Format: XX:XX:XX:XX:XX:XX</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Channel RFCOMM</label>
                <input type="number" name="bt_channel" id="f-bt-channel" placeholder="1" min="1" max="30"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Timeout (detik)</label>
                <input type="number" name="timeout" id="f-timeout" placeholder="5" min="1"
                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
              </div>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">RFCOMM Device</label>
              <input type="text" name="rfcomm_dev" id="f-rfcomm" placeholder="/dev/rfcomm0"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-white">
              <p class="text-xs text-gray-400 mt-1">Path device RFCOMM yang sudah di-bind</p>
            </div>
          </div>

          <!--  NETWORK fields  -->
          <div id="fields-network" class="space-y-4 hidden">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                IP Address <span class="text-red-500">*</span>
              </label>
              <input type="text" name="ip_address" id="f-ip" placeholder="192.168.1.100"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500 transition-all bg-white">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">Port</label>
              <input type="number" name="port" id="f-port" placeholder="9100" min="1" max="65535"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition-all bg-white">
              <p class="text-xs text-gray-400 mt-1">Default port ESC/POS: 9100</p>
            </div>
          </div>

          <!--  USB fields  -->
          <div id="fields-usb" class="space-y-4 hidden">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                USB Device Path <span class="text-red-500">*</span>
              </label>
              <input type="text" name="usb_device" id="f-usb" placeholder="/dev/usb/lp0"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all bg-white">
              <p class="text-xs text-gray-400 mt-1">Cek dengan: <code class="bg-gray-100 px-1 rounded">ls /dev/usb/</code></p>
            </div>
          </div>

          <!-- Status aktif (semua tipe) -->
          <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
            <div>
              <div class="text-sm font-semibold text-gray-700">Status Printer</div>
              <div class="text-xs text-gray-400">Aktifkan agar printer dapat digunakan</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="is_active" id="f-active" value="1" class="sr-only peer" checked>
              <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
            </label>
          </div>

        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 p-4 border-t border-gray-100">
          <button type="button" onclick="closeFormModal()"
            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Batal
          </button>
          <button type="submit" id="btn-submit"
            class="inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold text-white transition-colors shadow-sm bg-blue-600 hover:bg-blue-700">
            <i class="fa-solid fa-floppy-disk"></i>
            <span id="btn-submit-text">Simpan Printer</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!--  MODAL: Konfirmasi Hapus  -->
<div id="modal-delete" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
  <div class="relative w-full max-w-sm p-4">
    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
      <div class="p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
          <i class="fa-solid fa-trash-can text-red-500 text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Hapus Printer?</h3>
        <p class="text-sm text-gray-500 mb-1">Printer <strong id="delete-name" class="text-gray-800"></strong> akan dihapus permanen.</p>
        <p class="text-xs text-red-400">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <form method="POST" action="<?= BASE_URL ?>admin/printer/proses">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id" id="delete-id" value="">
        <div class="flex gap-3 px-6 pb-6">
          <button type="button" onclick="closeDeleteModal()"
            class="flex-1 rounded-lg border border-gray-200 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Batal
          </button>
          <button type="submit"
            class="flex-1 rounded-lg bg-red-600 py-2.5 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
            Ya, Hapus
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
//  Type config 
const TYPE_CONFIG = {
    1: { label: 'Bluetooth', icon: 'fa-solid fa-bluetooth',       badgeClass: 'bg-blue-50 text-blue-700 border-blue-200',   btnClass: 'bg-blue-600 hover:bg-blue-700' },
    2: { label: 'Network',   icon: 'fa-solid fa-network-wired',   badgeClass: 'bg-green-50 text-green-700 border-green-200', btnClass: 'bg-green-600 hover:bg-green-700' },
    3: { label: 'USB',       icon: 'fa-solid fa-plug',             badgeClass: 'bg-orange-50 text-orange-700 border-orange-200', btnClass: 'bg-orange-600 hover:bg-orange-700' },
};

function openTypePickerModal() {
    showModal('modal-type-picker');
}
function closeTypePickerModal() {
    hideModal('modal-type-picker');
}

//  Tambah 
function openAddModal(type) {
    closeTypePickerModal();
    resetForm();
    document.getElementById('form-action').value = 'tambah';
    document.getElementById('form-type').value   = type;
    document.getElementById('f-port').value       = (type === 2) ? '9100' : '';
    applyTypeUI(type, false);
    showModal('modal-form');
}

//  Edit 
function openEditModal(p) {
    resetForm();
    const type = parseInt(p.type);
    document.getElementById('form-action').value  = 'edit';
    document.getElementById('form-id').value      = p.id;
    document.getElementById('form-type').value    = type;
    document.getElementById('f-name').value       = p.name       || '';
    document.getElementById('f-active').checked   = p.is_active == 1;

    if (type === 1) {
        document.getElementById('f-bt-mac').value    = p.bt_mac     || '';
        document.getElementById('f-bt-channel').value= p.bt_channel || '';
        document.getElementById('f-rfcomm').value    = p.rfcomm_dev || '';
        document.getElementById('f-timeout').value   = p.timeout    || '';
    } else if (type === 2) {
        document.getElementById('f-ip').value        = p.ip_address || '';
        document.getElementById('f-port').value      = p.port       || '9100';
    } else if (type === 3) {
        document.getElementById('f-usb').value       = p.usb_device || '';
    }

    applyTypeUI(type, true);
    showModal('modal-form');
}

function applyTypeUI(type, isEdit) {
    const cfg = TYPE_CONFIG[type];

    // Title & icon
    document.getElementById('modal-icon').className        = cfg.icon + ' text-base';
    document.getElementById('modal-title-text').textContent = (isEdit ? 'Edit' : 'Tambah') + ' Printer ' + cfg.label;

    // Badge
    const badge = document.getElementById('type-badge');
    badge.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border ' + cfg.badgeClass;
    badge.innerHTML = `<i class="${cfg.icon}"></i> ${cfg.label}`;

    // Submit button color
    const btn = document.getElementById('btn-submit');
    btn.className = btn.className.replace(/bg-\w+-600 hover:bg-\w+-700/, cfg.btnClass);

    // Submit button text
    document.getElementById('btn-submit-text').textContent = isEdit ? 'Simpan Perubahan' : 'Simpan Printer';

    // Show/hide field groups
    document.getElementById('fields-bluetooth').classList.toggle('hidden', type !== 1);
    document.getElementById('fields-network').classList.toggle('hidden', type !== 2);
    document.getElementById('fields-usb').classList.toggle('hidden', type !== 3);

    // Required attributes
    document.getElementById('f-bt-mac').required  = (type === 1);
    document.getElementById('f-ip').required       = (type === 2);
    document.getElementById('f-usb').required      = (type === 3);
}

function closeFormModal() {
    hideModal('modal-form');
}

function resetForm() {
    document.getElementById('printer-form').reset();
    document.getElementById('f-active').checked = true;
}

//  Hapus 
function confirmDelete(id, name) {
    document.getElementById('delete-id').value          = id;
    document.getElementById('delete-name').textContent  = name;
    showModal('modal-delete');
}
function closeDeleteModal() {
    hideModal('modal-delete');
}

//  Helpers 
function showModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function hideModal(id) {
    const m = document.getElementById(id);
    m.classList.add('hidden');
    m.classList.remove('flex');
}

// Tutup modal klik luar
['modal-type-picker', 'modal-form', 'modal-delete'].forEach(id => {
    const modalElement = document.getElementById(id);
    if (modalElement) {
        modalElement.addEventListener('click', function(e) {
            const modalContent = this.querySelector('.bg-white');
            
            if (modalContent && !modalContent.contains(e.target)) {
                hideModal(id);
            }
        });
    }
});

// Auto-dismiss flash
setTimeout(() => {
    const f = document.getElementById('flash-msg');
    if (f) f.remove();
}, 4000);
</script>

<?php include '../layout/footer.php'; ?>
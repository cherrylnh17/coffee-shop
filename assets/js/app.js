/**
 * app.js
 * Trafa Coffee — Main JavaScript
 *
 * Catatan: getImageUrl() tersedia secara global dari assets/js/helperUrl.js
 * yang di-load sebelum file ini di index.php.
 * BASE_URL juga di-inject via <script> di index.php sebelum helperUrl.js di-load.
 */

// ─── Navbar Scroll Effect ───────────────────────────────────────────────────
function initNavbar() {
  var navbar = document.getElementById("navbar");
  if (!navbar) return;

  window.addEventListener("scroll", function () {
    if (window.scrollY > 60) {
      navbar.classList.add("nav-scrolled");
      navbar.classList.remove("nav-top");
    } else {
      navbar.classList.remove("nav-scrolled");
      navbar.classList.add("nav-top");
    }
  });
}

// ─── Smooth Scroll ──────────────────────────────────────────────────────────
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      var target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });
}

// ─── Render Menu Cards ──────────────────────────────────────────────────────
function renderMenuCards(menuData) {
  var container = document.getElementById("menu-grid");
  if (!container) return;

  if (!menuData || menuData.length === 0) {
    container.innerHTML =
      '<div class="col-span-full text-center py-16">' +
      '<div class="text-6xl mb-4">☕</div>' +
      '<p class="text-blue-200 text-lg">Menu sedang disiapkan...</p>' +
      "</div>";
    return;
  }

  container.innerHTML = menuData
    .map(function (item, index) {
      // getImageUrl() berasal dari assets/js/helperUrl.js (global)
      var imgSrc = getImageUrl(item.image);
      var desc =
        item.description ||
        "Sajian spesial dari barista kami yang berpengalaman.";

      return (
        '<div class="menu-card group relative bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden' +
        ' hover:border-blue-400/40 hover:bg-white/10 transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-blue-500/20"' +
        ' style="animation-delay:' +
        index * 100 +
        'ms">' +
        '<div class="relative overflow-hidden h-52">' +
        '<img src="' +
        imgSrc +
        '" alt="' +
        item.name +
        '"' +
        ' class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"' +
        " onerror=\"this.src='assets/images/placeholder.jpg'\" />" +
        '<div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent"></div>' +
        '<div class="absolute top-3 right-3 bg-blue-500/80 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1 rounded-full">☕ Pilihan</div>' +
        "</div>" +
        '<div class="p-5">' +
        '<h3 class="text-white font-bold text-lg mb-2 group-hover:text-blue-300 transition-colors duration-300">' +
        item.name +
        "</h3>" +
        '<p class="text-blue-200/70 text-sm leading-relaxed line-clamp-2">' +
        desc +
        "</p>" +
        '<div class="mt-4 flex items-center justify-between">' +
        '<span class="text-blue-400 text-xs font-medium tracking-wider uppercase">Lihat Detail</span>' +
        '<div class="w-7 h-7 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/40 transition-colors duration-300">' +
        '<svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>' +
        "</svg>" +
        "</div>" +
        "</div>" +
        "</div>" +
        "</div>"
      );
    })
    .join("");
}

// ─── QR Scanner Modal ───────────────────────────────────────────────────────
function initQRScanner() {
  var ids = [
    "open-qr-scanner",
    "open-qr-scanner-mobile",
    "open-qr-scanner-menu",
    "hero-order-btn",
    "cta-order-btn", 
  ];
  var modal = document.getElementById("qr-modal");
  var closeBtn = document.getElementById("close-qr-modal");
  var statusEl = document.getElementById("qr-status");

  var scanner = null;

  function openModal() {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    document.body.classList.add("overflow-hidden");
    startScanner();
  }

  function closeModal() {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    document.body.classList.remove("overflow-hidden");
    stopScanner();
  }

  function startScanner() {
    if (typeof Html5Qrcode === "undefined") {
      statusEl.textContent = "Library scanner tidak tersedia.";
      return;
    }

    // Pastikan tombol coba lagi dari error sebelumnya dihapus jika ada
    var oldRetryBtn = document.getElementById('retry-qr-btn');
    if (oldRetryBtn) oldRetryBtn.remove();

    scanner = new Html5Qrcode("qr-reader");
    var config = {
      fps: 10,
      qrbox: { width: 220, height: 220 },
      aspectRatio: 1.0,
    };

    statusEl.innerHTML = "Menginisialisasi kamera...";

    scanner.start(
      { facingMode: "environment" },
      config,
      function (decodedText) {
        var rawText = decodedText.trim();
        var isFullUrl = /^https?:\/\//i.test(rawText);
        var redirectUrl;
        var tableNumber;
        var appBaseUrl = (window.BASE_URL || "").replace(/\/$/, "");

        if (isFullUrl) {
          // ── Validasi Full URL ──
          try {
            var scannedUrl = new URL(rawText);
            var expectedUrl = new URL(appBaseUrl + "/");

            // Validasi domain: protocol, hostname, dan port harus sama
            if (scannedUrl.protocol !== expectedUrl.protocol ||
                scannedUrl.hostname !== expectedUrl.hostname ||
                scannedUrl.port !== expectedUrl.port) {
              stopScanner();
              showQRInvalidError("QR Code bukan dari domain ini. Silakan scan QR yang benar.");
              return;
            }

            // Validasi format URL: harus /order/{table}/menu
            var urlMatch = rawText.match(/\/order\/([^\/\?#]+)\/menu/);
            if (!urlMatch) {
              stopScanner();
              showQRInvalidError("Format QR tidak valid. Pastikan QR code benar.");
              return;
            }

            tableNumber = urlMatch[1];
            redirectUrl = rawText;
          } catch (e) {
            stopScanner();
            showQRInvalidError("Format QR tidak valid.");
            return;
          }
        } else {
          // ── Backward compatibility: QR berisi nomor meja saja ──
          tableNumber = rawText;
          var redirectOrder = (window.APP_REDIRECT || "order").replace(/^\/|\/$/g, "");
          redirectUrl = appBaseUrl + "/" + redirectOrder + "/" + tableNumber + "/menu";
        }

        // 1. Hentikan scanner sementara saat melakukan validasi
        stopScanner();
        statusEl.innerHTML = '<span class="text-blue-600 font-bold">⏳ Mengecek validasi meja...</span>';

        // 2. Request ke backend untuk cek nomor meja
        fetch(appBaseUrl + "/check_table.php?table_name=" + encodeURIComponent(tableNumber))
          .then(function(response) {
            return response.json();
          })
          .then(function(data) {
            if (data.status === 'success') {
              // Jika Valid -> Alihkan
              statusEl.innerHTML = '<span class="text-green-600 font-bold">✅ Meja ditemukan! Mengalihkan ke Meja ' + tableNumber + '...</span>';
              setTimeout(function () {
                window.location.href = redirectUrl;
              }, 800);
            } else {
              // Jika Nomor Meja Tidak Ditemukan
              showQRInvalidError("Nomor meja tidak ditemukan. Silakan cek nomor meja Anda.");
            }
          })
          .catch(function(error) {
            console.error("Error:", error);
            showQRInvalidError("Terjadi kesalahan jaringan. Silakan coba lagi.");
          });
      }
    ).catch(function(err){
      console.error("Camera start eror:", err);
      var msg = "Tidak Bisa Mengakses kamera.";
      if (err &&(err.name === "NotAllowedError" || /permission/i.test(String(err)))) {
        msg = "Izin Kamera Ditolak. Mohon izinkan akses kamera lalu klik tombol di bawah.";
      } else if(err && err.name === "NotFoundError") {
        msg = "Kamera Tidak Ditemukan di Perangkat Ini.";
      }
      showQRInvalidError(msg);
    });
  }

  // Fungsi tambahan untuk menampilkan error dan tombol Coba Lagi
  function showQRInvalidError(customMsg) {
    var errorMsg = customMsg || "QR Tidak Valid atau Meja Tidak Ditemukan.";
    statusEl.innerHTML = '<span class="text-red-600 font-bold">❌ ' + errorMsg + '</span>';
    
    // Cek agar tidak menduplikasi tombol
    if (!document.getElementById("retry-qr-btn")) {
      var retryBtn = document.createElement("button");
      retryBtn.id = "retry-qr-btn";
      retryBtn.className = "mt-4 bg-red-50 border border-red-200 text-red-600 hover:bg-red-600 hover:text-white font-semibold px-6 py-2 rounded-full transition-all duration-300 block mx-auto";
      retryBtn.innerText = "Coba Lagi Scanner";
      
      retryBtn.onclick = function() {
        this.remove(); // Hapus tombol
        statusEl.textContent = "Menginisialisasi kamera...";
        startScanner(); // Mulai ulang kamera
      };
      
      // Tambahkan tombol tepat di bawah status
      statusEl.parentNode.appendChild(retryBtn);
    }
  }

  function stopScanner() {
    if (scanner) {
      scanner
        .stop()
        .then(function () {
          scanner.clear();
          scanner = null;
        })
        .catch(function () {});
    }
  }

  ids.forEach(function (id) {
    var btn = document.getElementById(id);

    if (btn) {
      btn.addEventListener("click", openModal);
    }
  });
  if (closeBtn) closeBtn.addEventListener("click", closeModal);

  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) closeModal();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeModal();
  });
}

// ─── Intersection Observer Animations ──────────────────────────────────────
function initScrollAnimations() {
  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-in");
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1 },
  );

  document.querySelectorAll(".scroll-reveal").forEach(function (el) {
    observer.observe(el);
  });
}

// ─── Init All ───────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  initNavbar();
  initSmoothScroll();
  initScrollAnimations();
  initQRScanner();

  // Render menu dari data yang di-inject PHP
  if (window.TRAFA_MENU_DATA) {
    renderMenuCards(window.TRAFA_MENU_DATA);
  }
});

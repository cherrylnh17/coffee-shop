<?php
/**
 * index.php
 * Trafa Coffee — Landing Page
 */

require_once 'path.php';   // defines BASE_URL constant
require_once 'config.php'; // defines $pdo (PDO connection)

// ─── Fetch menu from database ───────────────────────────────────────────────
$menuItems = [];
try {
    $stmt = $pdo->query("SELECT name, image, description FROM menu LIMIT 6");
    $menuItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $menuItems = [];
}

$menuJson = json_encode($menuItems, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Trafa Coffee — Nikmati kopi favoritmu dengan pemesanan digital cepat dan praktis." />
  <title>Trafa Coffee — Digital Coffee Experience</title>

  <!-- Google Fonts: Playfair Display + DM Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Playfair Display"', 'Georgia', 'serif'],
            body: ['"DM Sans"', 'sans-serif'],
          },
          colors: {
            brand: {
              50:  '#eff6ff',
              100: '#dbeafe',
              200: '#bfdbfe',
              300: '#93c5fd',
              400: '#60a5fa',
              500: '#3b82f6',
              600: '#2563eb', // Primary Blue 600
              700: '#1d4ed8',
              800: '#1e3a8a',
              900: '#0f172a',
              950: '#060e1f',
            }
          }
        }
      }
    }
  </script>

  <!-- html5-qrcode CDN -->
  <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

  <!-- Inline-free custom Tailwind utilities via @layer -->
  <style type="text/tailwindcss">
    @layer base {
      html { font-family: 'DM Sans', sans-serif; }
      body { @apply bg-slate-50 text-slate-800 antialiased overflow-x-hidden; }
    }
    @layer utilities {
      .font-display { font-family: 'Playfair Display', Georgia, serif; }
      
      /* PERBAIKAN NAVBAR: Transisi yang lebih jelas antara posisi atas dan saat discroll */
      .nav-top { @apply bg-transparent py-4; }
      .nav-scrolled { @apply bg-white/95 backdrop-blur-lg border-b border-slate-200 shadow-md py-2; }
      .nav-link { @apply transition-colors duration-200; }
      
      .scroll-reveal { @apply opacity-0 translate-y-8 transition-all duration-700 ease-out; }
      .scroll-reveal.animate-in { @apply opacity-100 translate-y-0; }
      
      .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      .glass-card {
        @apply bg-white border border-slate-100 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition-all duration-300;
      }
    }
  </style>
</head>

<body>

<!-- ═══════════════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════════════════ -->
<header id="navbar" class="fixed top-0 left-0 right-0 z-50 nav-top transition-all duration-500">
  <nav class="max-w-7xl mx-auto px-5 lg:px-8 h-16 flex items-center justify-between">

    <!-- Logo -->
    <a href="#hero" class="flex items-center gap-2.5 group">
      <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center group-hover:bg-blue-700 transition-colors duration-200 shadow-md shadow-blue-600/20">
        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
          <path d="M2 21V19H20V21H2ZM20 8C20 8 18 10 12 10C6 10 4 8 4 8V3H20V8ZM6 5V7.5C7.2 7.83 9.33 8 12 8C14.67 8 16.8 7.83 18 7.5V5H6Z"/>
        </svg>
      </div>
      <span class="font-display font-bold text-xl text-slate-900 tracking-tight">Trafa <span class="text-blue-600">Coffee</span></span>
    </a>

    <!-- Desktop Nav Links -->
    <ul class="hidden md:flex items-center bg-white px-2 py-1.5 rounded-full border border-slate-200 shadow-sm shadow-slate-200/50">
      <li><a href="#hero" class="nav-link block px-6 py-2 rounded-full text-blue-600 text-sm font-bold bg-blue-50/50">Home</a></li>
      <li><a href="#about" class="nav-link block px-6 py-2 rounded-full text-slate-800 hover:text-blue-600 hover:bg-slate-50 text-sm font-semibold transition-all">Tentang</a></li>
      <li><a href="#menu" class="nav-link block px-6 py-2 rounded-full text-slate-800 hover:text-blue-600 hover:bg-slate-50 text-sm font-semibold transition-all">Menu</a></li>
      <li><a href="#steps" class="nav-link block px-6 py-2 rounded-full text-slate-800 hover:text-blue-600 hover:bg-slate-50 text-sm font-semibold transition-all">Cara Pesan</a></li>
    </ul>

    <!-- CTA Button -->
    <div class="hidden md:flex items-center gap-3">
      <button id="open-qr-scanner"
        class="bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-semibold px-6 py-2.5 rounded-full transition-all duration-200 shadow-lg shadow-blue-600/30">
        Order Sekarang
      </button>
    </div>

    <!-- Mobile hamburger -->
    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-800 hover:text-blue-600 hover:bg-slate-100 transition-colors duration-200 bg-white shadow-sm border border-slate-200" aria-label="Toggle Menu">
      <!-- Hamburger icon -->
      <svg class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
  </nav>
</header>

<!-- PERBAIKAN: Mobile Sidebar Menu & Overlay -->
<!-- Overlay Gelap -->
<div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300"></div>

<!-- Sidebar Container -->
<div id="mobile-sidebar" class="fixed top-0 right-0 h-full w-[80%] max-w-sm bg-white z-[70] transform translate-x-full transition-transform duration-300 shadow-2xl flex flex-col">
  
  <!-- Sidebar Header (Logo & Close Button) -->
  <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
    <div class="flex items-center gap-2.5 group">
      <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
        <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
          <path d="M2 21V19H20V21H2ZM20 8C20 8 18 10 12 10C6 10 4 8 4 8V3H20V8ZM6 5V7.5C7.2 7.83 9.33 8 12 8C14.67 8 16.8 7.83 18 7.5V5H6Z"/>
        </svg>
      </div>
      <span class="font-display font-bold text-lg text-slate-900">Trafa <span class="text-blue-600">Coffee</span></span>
    </div>
    
    <!-- Close Button -->
    <button id="close-sidebar-btn" class="p-2 bg-slate-50 text-slate-500 rounded-full hover:bg-red-50 hover:text-red-500 transition-colors">
      <svg class="w-5 h-5 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  <!-- Sidebar Links -->
  <div class="flex-1 overflow-y-auto px-6 py-6 flex flex-col gap-3">
    <a href="#hero" class="nav-sidebar-link block text-blue-600 bg-blue-50 text-base font-bold px-4 py-3.5 rounded-xl transition-all duration-200">Home</a>
    <a href="#about" class="nav-sidebar-link block text-slate-800 hover:text-blue-600 text-base font-semibold px-4 py-3.5 rounded-xl border border-transparent hover:border-slate-100 hover:bg-slate-50 transition-all duration-200">Tentang</a>
    <a href="#menu" class="nav-sidebar-link block text-slate-800 hover:text-blue-600 text-base font-semibold px-4 py-3.5 rounded-xl border border-transparent hover:border-slate-100 hover:bg-slate-50 transition-all duration-200">Menu</a>
    <a href="#steps" class="nav-sidebar-link block text-slate-800 hover:text-blue-600 text-base font-semibold px-4 py-3.5 rounded-xl border border-transparent hover:border-slate-100 hover:bg-slate-50 transition-all duration-200">Cara Pesan</a>
    
    <div class="mt-6 pt-6 border-t border-slate-100">
      <button id="open-qr-scanner-mobile"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-base font-semibold px-5 py-4 rounded-xl transition-all duration-200 text-center shadow-md shadow-blue-600/20 active:scale-95">
        Order Sekarang
      </button>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     HERO SECTION
════════════════════════════════════════════════════════════════ -->
<section id="hero" class="relative min-h-screen flex items-center overflow-hidden bg-slate-50 pt-20 pb-12 lg:py-0">
  <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-100 rounded-full blur-[100px] opacity-60 pointer-events-none -translate-y-1/2 translate-x-1/3"></div>
  <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-indigo-50 rounded-full blur-[80px] pointer-events-none translate-y-1/3 -translate-x-1/4"></div>
  <div class="absolute inset-0 pointer-events-none opacity-50" style="background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 40px 40px;"></div>

  <div class="relative max-w-7xl mx-auto px-5 lg:px-8 w-full">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center min-h-[85vh]">
      <!-- Text Content -->
      <div class="order-2 lg:order-1 space-y-8 text-center lg:text-left relative z-10">
        <div class="inline-flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-600 text-xs font-bold px-4 py-2 rounded-full">
          <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
          Digital Coffee Experience
        </div>
        <h1 class="font-display font-black text-5xl sm:text-6xl xl:text-7xl leading-[1.1] text-slate-900">
          Nikmati Kopi
          <br />
          <span class="text-blue-600">Favoritmu</span>
          <br />
          di Trafa Coffee
        </h1>
        <p class="text-slate-700 text-lg sm:text-xl leading-relaxed max-w-lg mx-auto lg:mx-0">
          Pesan kopi terbaik dengan mudah menggunakan <strong class="text-blue-600 font-semibold">QR Code</strong> di mejamu.
          Tanpa antre panjang, langsung ke tanganmu.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
          <a href="#menu"
             class="group w-full sm:w-auto flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-full transition-all duration-300 shadow-xl shadow-blue-600/20 hover:shadow-blue-600/40 hover:-translate-y-0.5 active:scale-95">
            Lihat Menu
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </a>
          <button id="hero-order-btn"
             class="group w-full sm:w-auto flex items-center justify-center gap-2 bg-white border-2 border-slate-200 hover:border-blue-600 text-slate-800 hover:text-blue-600 font-semibold px-8 py-4 rounded-full transition-all duration-300 hover:shadow-lg active:scale-95">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.243m-6.243 0H3.757"/>
            </svg>
            Order Sekarang
          </button>
        </div>

        <div class="flex flex-wrap justify-center lg:justify-start gap-8 pt-8 border-t border-slate-200 mt-8">
          <div>
            <div class="text-3xl font-display font-black text-slate-900">50+</div>
            <div class="text-slate-600 font-medium text-sm mt-1">Varian Menu</div>
          </div>
          <div class="w-px bg-slate-200 hidden sm:block"></div>
          <div>
            <div class="text-3xl font-display font-black text-slate-900">∞</div>
            <div class="text-slate-600 font-medium text-sm mt-1">Refill Semangat</div>
          </div>
          <div class="w-px bg-slate-200 hidden sm:block"></div>
          <div>
            <div class="text-3xl font-display font-black text-slate-900">24/7</div>
            <div class="text-slate-600 font-medium text-sm mt-1">Digital Order</div>
          </div>
        </div>
      </div>

      <!-- Hero Visual -->
      <div class="order-1 lg:order-2 flex items-center justify-center relative mt-10 lg:mt-0">
        <div class="relative w-full max-w-sm mx-auto">
          <div class="relative w-72 h-72 sm:w-96 sm:h-96 mx-auto">
            <div class="absolute inset-0 rounded-full border border-blue-200 border-dashed animate-spin" style="animation-duration: 25s;"></div>
            <div class="absolute inset-6 rounded-full border border-blue-100 animate-spin" style="animation-duration: 15s; animation-direction: reverse;"></div>
            <div class="absolute inset-12 rounded-full bg-white border border-slate-100 flex items-center justify-center shadow-2xl shadow-blue-600/10">
              <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-blue-50 to-white"></div>
              <svg class="w-28 h-28 text-blue-600 relative z-10" viewBox="0 0 100 100" fill="currentColor">
                <path d="M15 30 L85 30 L78 80 Q77 88 68 88 L32 88 Q23 88 22 80 Z" fill="rgba(37, 99, 235, 0.1)" stroke="currentColor" stroke-width="2.5"/>
                <path d="M78 38 Q95 38 95 52 Q95 66 78 66" fill="none" stroke="currentColor" stroke-width="2.5"/>
                <path d="M35 20 Q38 10 45 15 Q48 5 55 10 Q58 2 65 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="absolute top-10 right-10 w-4 h-4 bg-blue-500 rounded-full animate-bounce shadow-md shadow-blue-500/30" style="animation-delay: 0s;"></div>
            <div class="absolute bottom-16 left-8 w-3 h-3 bg-indigo-400 rounded-full animate-bounce shadow-md shadow-indigo-400/30" style="animation-delay: 0.5s;"></div>
          </div>
          <div class="absolute -top-6 -left-6 sm:-left-12 bg-white rounded-2xl px-5 py-4 shadow-xl border border-slate-100 animate-bounce" style="animation-duration: 4s;">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center text-xl">☕</div>
              <div>
                <div class="text-slate-900 text-sm font-bold">Americano</div>
                <div class="text-slate-600 text-xs font-medium">Baru Diseduh</div>
              </div>
            </div>
          </div>
          <div class="absolute -bottom-4 -right-4 sm:-right-8 bg-white rounded-2xl px-5 py-4 shadow-xl border border-slate-100 animate-bounce" style="animation-duration: 3.5s; animation-delay: 0.5s;">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-50 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <div>
                <div class="text-slate-900 text-sm font-bold">Order Diterima</div>
                <div class="text-green-600 text-xs font-medium">Sedang disiapkan</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     TENTANG KAMI
════════════════════════════════════════════════════════════════ -->
<section id="about" class="relative py-24 lg:py-32 bg-white">
  <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-16 scroll-reveal">
      <span class="text-blue-600 text-sm font-bold tracking-widest uppercase bg-blue-50 px-4 py-1.5 rounded-full inline-block">Tentang Kami</span>
      <h2 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 mt-6 mb-5">
        Lebih dari Sekadar
        <span class="text-blue-600"> Secangkir Kopi</span>
      </h2>
      <p class="text-slate-700 text-lg max-w-2xl mx-auto leading-relaxed">
        Trafa Coffee hadir sebagai ruang ketiga yang menggabungkan cita rasa kopi premium dengan teknologi pemesanan digital modern untuk kenyamanan Anda.
      </p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
      <!-- Card 1 -->
      <div class="scroll-reveal glass-card p-8 hover:-translate-y-2 group">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors duration-300">
          <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
          </svg>
        </div>
        <h3 class="text-slate-900 font-bold text-xl mb-3">Cafe Modern</h3>
        <p class="text-slate-700 text-sm leading-relaxed">Desain interior elegan dengan suasana nyaman yang menginspirasi kreativitasmu.</p>
      </div>
      <!-- Card 2 -->
      <div class="scroll-reveal glass-card p-8 hover:-translate-y-2 group" style="transition-delay: 100ms;">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors duration-300">
          <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.243m-6.243 0H3.757m12.485 0l3 3m-3-3l-3-3"/>
          </svg>
        </div>
        <h3 class="text-slate-900 font-bold text-xl mb-3">Pesan Digital</h3>
        <p class="text-slate-700 text-sm leading-relaxed">Scan QR di mejamu, pilih menu, dan bayar — semua dalam genggamanmu.</p>
      </div>
      <!-- Card 3 -->
      <div class="scroll-reveal glass-card p-8 hover:-translate-y-2 group" style="transition-delay: 200ms;">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors duration-300">
          <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </div>
        <h3 class="text-slate-900 font-bold text-xl mb-3">Cepat & Praktis</h3>
        <p class="text-slate-700 text-sm leading-relaxed">Proses order hanya hitungan detik. Tanpa antre, tanpa ribet.</p>
      </div>
      <!-- Card 4 -->
      <div class="scroll-reveal glass-card p-8 hover:-translate-y-2 group" style="transition-delay: 300ms;">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors duration-300">
          <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <h3 class="text-slate-900 font-bold text-xl mb-3">Tempat Nongkrong</h3>
        <p class="text-slate-700 text-sm leading-relaxed">WiFi kencang, soket listrik, suasana produktif sepanjang hari.</p>
      </div>
    </div>

    <!-- Bottom visual strip -->
    <div class="mt-16 scroll-reveal">
      <div class="bg-blue-600 rounded-3xl p-8 lg:p-12 flex flex-col lg:flex-row items-center gap-8 overflow-hidden relative shadow-2xl shadow-blue-600/20">
        <div class="absolute inset-0 opacity-[0.1]" style="background-image: radial-gradient(circle, white 2px, transparent 2px); background-size: 30px 30px;"></div>
        <div class="absolute right-0 top-0 bottom-0 w-1/2 bg-gradient-to-l from-white/10 to-transparent pointer-events-none"></div>
        <div class="flex-1 space-y-3 relative z-10 text-center lg:text-left">
          <h3 class="font-display font-black text-3xl text-white">Tempatmu untuk Berkarya</h3>
          <p class="text-blue-100 text-base leading-relaxed max-w-xl mx-auto lg:mx-0">Dari pagi hingga malam, Trafa Coffee selalu siap menemanimu dengan kopi terbaik dan suasana yang selalu menyenangkan.</p>
        </div>
        <div class="flex gap-8 shrink-0 relative z-10 bg-white/10 backdrop-blur-md px-8 py-5 rounded-2xl border border-white/20">
          <div class="text-center">
            <div class="text-4xl font-display font-black text-white">100%</div>
            <div class="text-blue-100 font-medium text-xs mt-1">Arabica Premium</div>
          </div>
          <div class="w-px bg-white/20"></div>
          <div class="text-center">
            <div class="text-4xl font-display font-black text-white">5★</div>
            <div class="text-blue-100 font-medium text-xs mt-1">Customer Rating</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     SAMPLE MENU (PERBAIKAN: Teks Solid & Hapus Tombol Detail)
════════════════════════════════════════════════════════════════ -->
<section id="menu" class="relative py-24 lg:py-32 bg-slate-50 border-y border-slate-200/60">
  <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-16 scroll-reveal">
      <span class="text-blue-600 text-sm font-bold tracking-widest uppercase bg-blue-100/50 px-4 py-1.5 rounded-full inline-block">Menu Pilihan</span>
      <h2 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 mt-6 mb-5">
        Racikan
        <span class="text-blue-600"> Terbaik Kami</span>
      </h2>
      <p class="text-slate-700 text-lg max-w-md mx-auto">Dibuat dengan biji kopi premium pilihan dan cinta dari barista kami.</p>
    </div>

    <!-- ID diubah menjadi menu-grid-override agar app.js tidak menimpanya -->
    <!-- Saya menyuntikkan script khusus di bawah untuk merender secara statis dan solid -->
    <div id="menu-grid-override" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <!-- Loading skeleton -->
      <?php for ($i = 0; $i < 6; $i++): ?>
      <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm animate-pulse">
        <div class="h-56 bg-slate-200"></div>
        <div class="p-6 space-y-4">
          <div class="h-5 bg-slate-200 rounded-full w-3/4"></div>
          <div class="space-y-2">
            <div class="h-3 bg-slate-100 rounded-full w-full"></div>
            <div class="h-3 bg-slate-100 rounded-full w-5/6"></div>
          </div>
        </div>
      </div>
      <?php endfor; ?>
    </div>

    <div class="text-center mt-16 scroll-reveal">
      <button id="open-qr-scanner-menu"
        class="group inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-blue-600 text-slate-800 hover:text-blue-600 font-bold px-10 py-4 rounded-full transition-all duration-300 hover:shadow-lg w-full sm:w-auto">
        Lihat Semua Menu via QR
        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     CARA PEMESANAN
════════════════════════════════════════════════════════════════ -->
<section id="steps" class="relative py-24 lg:py-32 bg-white overflow-hidden">
  <div class="relative max-w-7xl mx-auto px-5 lg:px-8">
    <div class="text-center mb-20 scroll-reveal">
      <span class="text-blue-600 text-sm font-bold tracking-widest uppercase bg-blue-50 px-4 py-1.5 rounded-full inline-block">Cara Pesan</span>
      <h2 class="font-display font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 mt-6 mb-5">
        Sesimple
        <span class="text-blue-600"> 3 Langkah</span>
      </h2>
      <p class="text-slate-700 text-lg max-w-lg mx-auto">Tidak perlu repot antri atau memanggil pelayan. Semuanya dari HP-mu.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-12 relative">
      <div class="hidden md:block absolute top-[3.5rem] left-[15%] right-[15%] h-0.5 bg-gradient-to-r from-blue-100 via-blue-300 to-blue-100 pointer-events-none"></div>

      <!-- Step 1 -->
      <div class="scroll-reveal flex flex-col items-center text-center group">
        <div class="relative mb-8 bg-white z-10 px-4">
          <div class="w-24 h-24 bg-white border-2 border-blue-100 rounded-3xl flex items-center justify-center group-hover:border-blue-600 group-hover:-translate-y-2 transition-all duration-300 shadow-xl shadow-slate-200">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.243m-6.243 0H3.757"/>
            </svg>
          </div>
          <div class="absolute -top-3 -right-1 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-600/40">1</div>
        </div>
        <h3 class="text-slate-900 font-black text-2xl mb-4">Scan QR di Meja</h3>
        <p class="text-slate-700 text-base leading-relaxed max-w-[16rem]">Buka kamera HP dan scan QR code yang tersedia di mejamu untuk membuka menu digital.</p>
      </div>

      <!-- Step 2 -->
      <div class="scroll-reveal flex flex-col items-center text-center group" style="transition-delay: 150ms;">
        <div class="relative mb-8 bg-white z-10 px-4">
          <div class="w-24 h-24 bg-white border-2 border-blue-100 rounded-3xl flex items-center justify-center group-hover:border-blue-600 group-hover:-translate-y-2 transition-all duration-300 shadow-xl shadow-slate-200">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <div class="absolute -top-3 -right-1 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-600/40">2</div>
        </div>
        <h3 class="text-slate-900 font-black text-2xl mb-4">Pilih Pesanan</h3>
        <p class="text-slate-700 text-base leading-relaxed max-w-[16rem]">Jelajahi menu lengkap, pilih favoritmu, dan tambahkan ke keranjang dengan mudah.</p>
      </div>

      <!-- Step 3 -->
      <div class="scroll-reveal flex flex-col items-center text-center group" style="transition-delay: 300ms;">
        <div class="relative mb-8 bg-white z-10 px-4">
          <div class="w-24 h-24 bg-white border-2 border-blue-100 rounded-3xl flex items-center justify-center group-hover:border-blue-600 group-hover:-translate-y-2 transition-all duration-300 shadow-xl shadow-slate-200">
            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <div class="absolute -top-3 -right-1 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-lg shadow-blue-600/40">3</div>
        </div>
        <h3 class="text-slate-900 font-black text-2xl mb-4">Bayar & Tunggu</h3>
        <p class="text-slate-700 text-base leading-relaxed max-w-[16rem]">Pilih metode pembayaran favoritmu. Pesanan langsung diproses dan diantar ke mejamu!</p>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     CTA ORDER
════════════════════════════════════════════════════════════════ -->
<section class="relative py-24 lg:py-32 overflow-hidden bg-blue-600">
  <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500 rounded-full blur-[80px] opacity-60"></div>
  <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-700 rounded-full blur-[80px] opacity-60"></div>
  <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 2px, transparent 2px); background-size: 30px 30px;"></div>

  <div class="relative max-w-4xl mx-auto px-5 lg:px-8 text-center">
    <div class="scroll-reveal">
      <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-white text-xs font-bold px-4 py-2 rounded-full mb-8 backdrop-blur-md">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
        Siap Memesan?
      </div>
      <h2 class="font-display font-black text-4xl sm:text-5xl lg:text-6xl text-white mb-6 leading-tight">
        Kopi Impianmu
        <br />
        Hanya Satu Scan
      </h2>
      <p class="text-blue-100 text-lg sm:text-xl mb-12 max-w-2xl mx-auto leading-relaxed">
        Scan QR code di mejamu sekarang dan nikmati pengalaman memesan kopi yang berbeda — cepat, mudah, dan menyenangkan.
      </p>
      <button id="cta-order-btn"
        class="group relative inline-flex items-center gap-3 bg-white hover:bg-slate-50 text-blue-600 font-bold text-lg px-10 py-5 rounded-full transition-all duration-300 shadow-2xl hover:shadow-white/20 hover:-translate-y-1 active:scale-95 overflow-hidden">
        <span class="relative z-10 flex items-center gap-3">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.243m-6.243 0H3.757"/>
          </svg>
          Mulai Order Sekarang
          <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
          </svg>
        </span>
      </button>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════════════════════════ -->
<footer class="relative bg-white border-t border-slate-200">
  <div class="max-w-7xl mx-auto px-5 lg:px-8 py-16">
    <div class="grid md:grid-cols-3 gap-12 mb-12">
      <div class="space-y-5">
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-600/20">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
              <path d="M2 21V19H20V21H2ZM20 8C20 8 18 10 12 10C6 10 4 8 4 8V3H20V8ZM6 5V7.5C7.2 7.83 9.33 8 12 8C14.67 8 16.8 7.83 18 7.5V5H6Z"/>
            </svg>
          </div>
          <span class="font-display font-black text-2xl text-slate-900">Trafa <span class="text-blue-600">Coffee</span></span>
        </div>
        <p class="text-slate-700 text-sm leading-relaxed max-w-xs">
          Cafe modern dengan pengalaman memesan digital yang cepat, mudah, dan menyenangkan.
        </p>
      </div>
      <div>
        <h4 class="text-slate-900 font-bold text-sm mb-6 tracking-wider uppercase">Navigasi</h4>
        <ul class="space-y-4">
          <li><a href="#hero" class="text-slate-700 hover:text-blue-600 font-medium transition-colors duration-200">Home</a></li>
          <li><a href="#about" class="text-slate-700 hover:text-blue-600 font-medium transition-colors duration-200">Tentang Kami</a></li>
          <li><a href="#menu" class="text-slate-700 hover:text-blue-600 font-medium transition-colors duration-200">Menu</a></li>
          <li><a href="#steps" class="text-slate-700 hover:text-blue-600 font-medium transition-colors duration-200">Cara Pesan</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-slate-900 font-bold text-sm mb-6 tracking-wider uppercase">Ikuti Kami</h4>
        <div class="flex gap-4">
          <a href="#" class="w-10 h-10 bg-white border border-slate-200 hover:border-blue-600 hover:bg-blue-600 rounded-xl flex items-center justify-center text-slate-500 hover:text-white transition-all duration-300 shadow-sm" aria-label="Sosmed">
             <!-- Icon Placeholders -->
             <span class="font-bold">IG</span>
          </a>
        </div>
      </div>
    </div>
    <div class="border-t border-slate-200 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-slate-600 text-sm font-medium">
        &copy; <?= date('Y') ?> Trafa Coffee. Semua hak dilindungi.
      </p>
      <p class="text-slate-600 text-sm font-medium">Dibuat dengan ❤️ untuk para pecinta kopi</p>
    </div>
  </div>
</footer>


<!-- ═══════════════════════════════════════════════════════════════
     QR SCANNER MODAL
════════════════════════════════════════════════════════════════ -->
<div id="qr-modal"
     class="hidden fixed inset-0 z-[999] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-5">
  <div class="relative w-full max-w-sm bg-white rounded-3xl p-6 lg:p-8 shadow-2xl">
    <button id="close-qr-modal"
      class="absolute top-4 right-4 w-10 h-10 bg-slate-100 hover:bg-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition-all duration-200">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-blue-50 border border-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.243m-6.243 0H3.757"/>
        </svg>
      </div>
      <h3 class="text-slate-900 font-bold text-2xl">Scan QR Code</h3>
      <p class="text-slate-600 text-sm mt-1">Arahkan kamera ke QR code di meja Anda</p>
    </div>
    <div id="qr-reader" class="rounded-xl overflow-hidden bg-slate-100 border border-slate-200" style="min-height: 260px;"></div>
    <div id="qr-status" class="text-center text-slate-500 font-medium text-sm mt-5 min-h-[20px]">
      Menginisialisasi kamera...
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     INJECT MENU DATA + SCRIPTS + INTERACTION LOGIC
════════════════════════════════════════════════════════════════ -->
<script>
  var BASE_URL = "<?= rtrim(BASE_URL, '/') ?>";
  window.TRAFA_MENU_DATA = <?= $menuJson ?>;
</script> 

<script src="assets/js/helperUrl.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    
    // 1. SCROLL REVEAL ANIMATION
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.15 };
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          observer.unobserve(entry.target); 
        }
      });
    }, observerOptions);

    document.querySelectorAll('.scroll-reveal').forEach((el) => {
      observer.observe(el);
    });

    // 2. NAVBAR SCROLL EFFECT
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 20) {
        navbar.classList.remove('nav-top');
        navbar.classList.add('nav-scrolled');
      } else {
        navbar.classList.add('nav-top');
        navbar.classList.remove('nav-scrolled');
      }
    });

    // 3. MOBILE SIDEBAR TOGGLE (PERBAIKAN)
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const closeSidebarBtn = document.getElementById('close-sidebar-btn');
    const sidebar = document.getElementById('mobile-sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    function openSidebar() {
      sidebarOverlay.classList.remove('hidden');
      setTimeout(() => sidebarOverlay.classList.remove('opacity-0'), 10);
      sidebar.classList.remove('translate-x-full');
    }

    function closeSidebar() {
      sidebar.classList.add('translate-x-full');
      sidebarOverlay.classList.add('opacity-0');
      setTimeout(() => sidebarOverlay.classList.add('hidden'), 300);
    }

    if (mobileBtn) mobileBtn.addEventListener('click', openSidebar);
    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

    // Tutup menu saat link di sidebar diklik
    document.querySelectorAll('.nav-sidebar-link').forEach(link => {
      link.addEventListener('click', closeSidebar);
    });

    // 4. QR SCANNER TRIGGERS
    const qrModal = document.getElementById('qr-modal');
    function openQrModal() {
      if (qrModal) {
        qrModal.classList.remove('hidden');
        qrModal.classList.add('flex');
        if(typeof closeSidebar === 'function') closeSidebar(); // tutup sidebar jika qr dibuka dr HP
      }
    }
    
    var ids = ['open-qr-scanner', 'open-qr-scanner-mobile', 'open-qr-scanner-menu', 'hero-order-btn', 'cta-order-btn'];
    ids.forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.addEventListener('click', openQrModal);
    });

    // 5. RENDER MENU STATIS LOKAL (PERBAIKAN TAMPILAN KARTU)
    // Script ini menimpa/mengganti app.js hanya untuk section card di index.php
    // Membuat font/teks menjadi warna solid gelap tanpa opacity dan menghilangkan "Lihat Detail"
    const menuGridOverride = document.getElementById('menu-grid-override');
    if (menuGridOverride && window.TRAFA_MENU_DATA && window.TRAFA_MENU_DATA.length > 0) {
      let menuHTML = '';
      window.TRAFA_MENU_DATA.forEach(item => {
        // Tentukan path gambar
        let imgSrc = (typeof getImageUrl === 'function') ? getImageUrl(item.image) : (BASE_URL + '/assets/img/menu/' + item.image);
        
        // Tampilan card HTML baru (Teks hitam solid dan tanpa link lihat detail)
        menuHTML += `
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 flex flex-col group">
          <div class="h-56 bg-slate-100 overflow-hidden relative">
            <img src="${imgSrc}" alt="${item.name}" class="w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700" onerror="this.src='https://via.placeholder.com/400x300?text=Kopi'">
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>
          <div class="p-6 flex flex-col flex-grow bg-white">
            <h3 class="text-xl font-black text-slate-900 mb-2 leading-tight">${item.name}</h3>
            <p class="text-slate-700 text-sm leading-relaxed line-clamp-2">${item.description}</p>
          </div>
        </div>
        `;
      });
      menuGridOverride.innerHTML = menuHTML;
    }
  });
</script>

<!-- Main app (Tetap dibiarkan namun bagian menu-grid sudah di-handle script di atas) -->
<script src="assets/js/app.js"></script>

</body>
</html>
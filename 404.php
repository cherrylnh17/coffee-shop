<?php
require_once('path.php');
$request_uri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/');

http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Halaman Tidak Ditemukan</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            display: ['"Playfair Display"', 'serif'],
            sans: ['"DM Sans"', 'sans-serif'],
          },
          colors: {
            coffee: {
              bg: '#0e0b08',
              surface: '#1a1510',
              border: '#2e2318',
              cream: '#f0e6d3',
              muted: '#7a6a55',
              accent: '#c8873a',
              light: '#e8a85c',
              text: '#e8ddd0',
            }
          },
          keyframes: {
            fadeUp: {
              '0%': {
                opacity: '0',
                transform: 'translateY(24px)'
              },
              '100%': {
                opacity: '1',
                transform: 'translateY(0)'
              },
            },
            float: {
              '0%, 100%': {
                transform: 'translateY(0px)'
              },
              '50%': {
                transform: 'translateY(-10px)'
              },
            },
            steam: {
              '0%': {
                opacity: '0',
                transform: 'translateY(0) scaleX(1)'
              },
              '50%': {
                opacity: '1',
                transform: 'translateY(-5px) scaleX(1.1)'
              },
              '100%': {
                opacity: '0',
                transform: 'translateY(-10px) scaleX(0.8)'
              },
            },
            glow: {
              '0%, 100%': {
                opacity: '0.06'
              },
              '50%': {
                opacity: '0.14'
              },
            }
          },
          animation: {
            'fade-up': 'fadeUp 0.7s cubic-bezier(0.16,1,0.3,1) both',
            'fade-up-1': 'fadeUp 0.7s 0.1s cubic-bezier(0.16,1,0.3,1) both',
            'fade-up-2': 'fadeUp 0.7s 0.2s cubic-bezier(0.16,1,0.3,1) both',
            'fade-up-3': 'fadeUp 0.7s 0.3s cubic-bezier(0.16,1,0.3,1) both',
            'fade-up-4': 'fadeUp 0.7s 0.4s cubic-bezier(0.16,1,0.3,1) both',
            'float': 'float 4s ease-in-out infinite',
            'steam': 'steam 2s ease-in-out infinite',
            'steam2': 'steam 2s 0.5s ease-in-out infinite',
            'glow': 'glow 4s ease-in-out infinite',
          }
        }
      }
    }
  </script>
  <style>
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
      pointer-events: none;
      z-index: 0;
    }
  </style>
</head>

<body class="bg-coffee-bg text-coffee-text font-sans min-h-screen flex items-center justify-center overflow-hidden relative">

  <div class="animate-glow fixed w-[600px] h-[600px] rounded-full pointer-events-none z-0"
    style="background: radial-gradient(circle, rgba(200,135,58,0.15) 0%, transparent 70%); top:50%; left:50%; transform:translate(-50%,-50%)">
  </div>

  <div class="relative z-10 text-center px-6 py-10 max-w-lg w-full">

    <div class="animate-fade-up inline-flex items-center justify-center w-24 h-24 rounded-full bg-coffee-surface border border-coffee-border mb-8 animate-float">
      <svg class="w-12 h-12" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path class="animate-steam" d="M15 8 Q16 5 15 3" stroke="#c8873a" stroke-width="1.5" stroke-linecap="round" />
        <path class="animate-steam2" d="M22 8 Q23 5 22 3" stroke="#c8873a" stroke-width="1.5" stroke-linecap="round" />
        <path d="M8 14h22l-3 16H11L8 14z" stroke="#c8873a" stroke-width="1.5" stroke-linejoin="round" fill="rgba(200,135,58,0.08)" />
        <path d="M30 18h3a4 4 0 010 8h-3" stroke="#c8873a" stroke-width="1.5" stroke-linecap="round" />
        <path d="M5 30h28" stroke="#c8873a" stroke-width="1.5" stroke-linecap="round" />
        <ellipse cx="19" cy="31.5" rx="10" ry="2" stroke="#c8873a" stroke-width="1.5" fill="none" />
      </svg>
    </div>

    <h2 class="animate-fade-up-1 font-display font-black leading-none tracking-tighter mb-2"
      style="font-size: clamp(6rem,20vw,9rem); background: linear-gradient(135deg, #c8873a 0%, #e8a85c 50%, #f0e6d3 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
      404
    </h2>

    <div class="animate-fade-up-1 flex items-center gap-3 max-w-[220px] mx-auto my-5">
      <div class="flex-1 h-px bg-gradient-to-r from-transparent via-coffee-border to-transparent"></div>
      <span class="text-coffee-accent text-lg">☕</span>
      <div class="flex-1 h-px bg-gradient-to-r from-transparent via-coffee-border to-transparent"></div>
    </div>

    <h1 class="animate-fade-up-2 font-display text-2xl md:text-3xl font-bold text-coffee-cream mb-3">
      Halaman Tidak Ditemukan
    </h1>

    <p class="animate-fade-up-2 text-coffee-muted text-sm leading-relaxed mb-2">
      Sepertinya halaman yang kamu cari sudah habis,<br>atau memang belum pernah ada.
    </p>

    <div class="animate-fade-up-3 inline-block mt-3 mb-7 px-4 py-1.5 bg-coffee-surface border border-coffee-border rounded-full text-xs font-mono text-coffee-muted break-all max-w-full">
      <span class="text-coffee-accent">GET</span> <?= $request_uri ?>
    </div>

    <div class="animate-fade-up-4 flex flex-wrap gap-3 justify-center">
      <a href="<?= BASE_URL ?>"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:-translate-y-0.5"
        style="background: linear-gradient(135deg, #c8873a, #e8a85c); color: #1a0f00;"
        onmouseover="this.style.boxShadow='0 8px 24px rgba(200,135,58,0.3)'"
        onmouseout="this.style.boxShadow='none'">
        Kembali ke Beranda
      </a>
      <a href="javascript:history.back()"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-medium bg-coffee-surface border border-coffee-border text-coffee-text transition-all duration-200 hover:-translate-y-0.5 hover:border-coffee-accent hover:text-coffee-accent">
        Halaman Sebelumnya
      </a>
    </div>

  </div>

</body>

</html>
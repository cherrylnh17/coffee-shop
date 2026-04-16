<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Träffa Coffee | Selamat Datang</title>
    <meta name="description" content="Selamat datang di Träffa Coffee. Tempat terbaik untuk menikmati secangkir kopi pilihan.">
    
    <!-- Google Fonts: Playfair Display (Serif) & Inter (Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Custom Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        coffee: {
                            50: '#fdf8f6',
                            100: '#f2e8e5',
                            200: '#eaddd7',
                            300: '#e0cec7',
                            400: '#d2bab0',
                            500: '#a37b68',
                            600: '#8c624d',
                            700: '#6b4632',
                            800: '#4a3022',
                            900: '#2d1c15',
                        },
                        amber: {
                            500: '#d97706',
                            600: '#b45309',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 1s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Utilities */
        .glass-nav {
            background: rgba(29, 18, 14, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hero-bg {
            background-image: url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=2071&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans text-stone-800 antialiased bg-coffee-50 selection:bg-coffee-500 selection:text-white">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-4 bg-transparent">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-mug-hot text-amber-500 text-2xl"></i>
                    <span class="font-serif font-bold text-2xl tracking-wide text-white">Träffa<span class="text-amber-500">.</span></span>
                </div>
                
                <!-- Badge (Optional/From original) -->
                <div class="hidden md:flex items-center">
                    <span class="px-3 py-1 rounded-full text-xs font-medium tracking-wide bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        v1.0.0
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="relative min-h-screen flex items-center justify-center hero-bg overflow-hidden">
        <!-- Dark Overlay for better text readability -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-coffee-900/90 z-0"></div>

        <!-- Content -->
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto mt-16">
            
            <!-- Small Intro -->
            <p class="text-amber-500 font-medium tracking-[0.2em] uppercase text-sm mb-4 opacity-0 animate-[fadeInUp_1s_ease-out_0.2s_forwards]">
                Kopi Pilihan, Suasana Nyaman
            </p>

            <!-- Main Heading -->
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-white leading-tight mb-6 opacity-0 animate-[fadeInUp_1s_ease-out_0.4s_forwards]">
                Selamat Datang di <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600 italic pr-2">
                    Träffa Coffee
                </span>
            </h1>

            <!-- Subheading -->
            <p class="text-gray-300 text-base md:text-lg lg:text-xl mb-10 max-w-2xl mx-auto font-light leading-relaxed opacity-0 animate-[fadeInUp_1s_ease-out_0.6s_forwards]">
                Rasakan pengalaman menikmati kopi otentik yang diseduh dengan penuh gairah. Temukan rasa favoritmu dan buat cerita baru bersama kami.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12 opacity-0 animate-[fadeInUp_1s_ease-out_0.8s_forwards]">
                <a href="users/index.php" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-amber-600 text-white font-medium hover:bg-amber-500 transition-all duration-300 shadow-[0_0_20px_rgba(217,119,6,0.4)] hover:shadow-[0_0_30px_rgba(217,119,6,0.6)] flex items-center justify-center gap-2">
                    <i class="fa-solid fa-compass text-sm"></i>
                    Eksplorasi Menu
                </a>
                <a href="auth/login.php" class="w-full sm:w-auto px-8 py-3.5 rounded-full bg-white/10 text-white font-medium border border-white/20 hover:bg-white hover:text-coffee-900 transition-all duration-300 backdrop-blur-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user text-sm"></i>
                    Masuk Akun
                </a>
            </div>

            <!-- Rating Section -->
            <div class="flex flex-col items-center justify-center opacity-0 animate-[fadeInUp_1s_ease-out_1s_forwards]">
                <div class="flex gap-1 text-amber-400 text-lg mb-2 drop-shadow-md">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <p class="text-gray-400 text-sm">
                    <span class="text-white font-medium">4.8/5</span> dari 2,000+ penikmat kopi
                </p>
            </div>
            
        </div>

        <!-- Scroll Down Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10 animate-bounce">
            <i class="fa-solid fa-chevron-down text-white/50 text-xl"></i>
        </div>
    </header>

    <!-- Script to handle Navbar Background on Scroll -->
    <script>
        const navbar = document.getElementById('navbar');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('glass-nav', 'py-3');
                navbar.classList.remove('bg-transparent', 'py-4');
            } else {
                navbar.classList.add('bg-transparent', 'py-4');
                navbar.classList.remove('glass-nav', 'py-3');
            }
        });
    </script>
</body>
</html>
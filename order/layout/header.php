<?php
require_once __DIR__ . '/../../path.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Beranda'; ?> - Trafa Coffee | Tempat Cafe kekinian di Indonesia</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Logo app -->
    <link rel="icon" href="<?= BASE_URL ?>assets/image/icon.png" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CDN Font awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        referrerpolicy="no-referrer" />
    <!-- sweetalert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        jakarta: ['Plus Jakarta Sans', 'sans-serif'],
                    },

                    keyframes: {
                        slideUp: {
                            '0%': {
                                transform: 'translateX(-50%) translateY(100%)',
                                opacity: '0',
                            },
                            '100%': {
                                transform: 'translateX(-50%) translateY(0)',
                                opacity: '1',
                            },
                        },

                        fadeIn: {
                            '0%': {
                                opacity: '0',
                                transform: 'translateY(10px)',
                            },
                            '100%': {
                                opacity: '1',
                                transform: 'translateY(0)',
                            },
                        },

                        pulseSlow: {
                            '0%, 100%': {
                                opacity: '1',
                                transform: 'scale(1)',
                            },
                            '50%': {
                                opacity: '0.6',
                                transform: 'scale(1.05)',
                            },
                        },
                    },

                    animation: {
                        'slide-up': 'slideUp 0.3s ease-out forwards',
                        'fade-in': 'fadeIn 0.25s ease-out forwards',
                        'pulse-slow': 'pulseSlow 3s ease-in-out infinite',
                    },
                },
            },
        };
    </script>
</head>

<body class="antialiased text-gray-800">
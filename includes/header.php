<?php
require_once 'config/database.php';
require_once 'classes/AssetManager.php';

// Set production-ready cache control headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- DataTables Core CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style type="text/tailwindcss">
        @tailwind base;
        @tailwind components;
        @tailwind utilities;

        @layer base {
          body {
            @apply antialiased text-gray-800 bg-gray-50;
          }
        }

        @layer components {
          .card-premium {
            @apply bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-shadow duration-300 hover:shadow-md;
          }

          .btn-primary {
            @apply bg-brand text-white px-5 py-2.5 rounded-xl font-semibold shadow-sm hover:bg-brand-dark focus:ring-2 focus:ring-brand focus:ring-offset-2 transition-all transform active:scale-95 flex items-center justify-center gap-2;
          }

          .btn-secondary {
            @apply bg-white text-gray-700 border border-gray-300 px-5 py-2.5 rounded-xl font-semibold shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-gray-200 focus:ring-offset-2 transition-all transform active:scale-95 flex items-center justify-center gap-2;
          }
          
          .btn-danger {
            @apply bg-red-50 text-red-600 border border-red-200 px-5 py-2.5 rounded-xl font-semibold shadow-sm hover:bg-red-100 focus:ring-2 focus:ring-red-200 focus:ring-offset-2 transition-all transform active:scale-95 flex items-center justify-center gap-2;
          }

          .input-premium {
            @apply w-full border-3 border-gray-200 rounded-xl shadow-sm focus:border-brand focus:ring focus:ring-brand focus:ring-opacity-50 px-4 py-2.5 transition-colors;
          }
        }

        @layer utilities {
          .scrollbar-hide::-webkit-scrollbar { display: none; }
          .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
          .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
          .scrollbar-thin::-webkit-scrollbar-track { @apply bg-gray-100 rounded-full; }
          .scrollbar-thin::-webkit-scrollbar-thumb { @apply bg-gray-300 rounded-full hover:bg-gray-400; }
        }

        .page-animate {
          animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUpFade {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
        }

        .spinner-border {
          @apply animate-spin rounded-full h-8 w-8 border-b-2 border-brand;
        }

        /* DataTables Overrides */
        .dataTables_wrapper { @apply pt-4; }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter { @apply mb-6 px-2; }
        .dataTables_wrapper .dataTables_length select { @apply border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none mx-2 bg-white text-sm; }
        .dataTables_wrapper .dataTables_filter input { @apply border border-gray-200 rounded-xl px-4 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none ml-2 bg-white text-sm min-w-[250px] transition-all; }
        .dataTables_wrapper .dataTables_info { @apply py-6 text-sm text-gray-500 font-medium px-2; }
        .dataTables_wrapper .dataTables_paginate { @apply py-6 flex justify-end gap-1 px-2; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { @apply px-4 py-2 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 hover:text-brand cursor-pointer transition-all duration-200 bg-white shadow-sm; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current { @apply bg-brand text-white border-brand hover:bg-brand-dark hover:text-white shadow-brand/20 shadow-lg; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { @apply opacity-50 cursor-not-allowed hover:bg-white hover:text-gray-600 shadow-none; }

        table.dataTable { @apply border-collapse !important; margin-top: 0 !important; margin-bottom: 0 !important; }
        table.dataTable.no-footer { border-bottom: 1px solid #f3f4f6 !important; }
        table.dataTable thead th { @apply px-6 py-4 bg-gray-50/80 text-gray-500 font-bold uppercase tracking-wider text-xs border-b border-gray-200 !important; }
        table.dataTable tbody td { @apply px-6 py-4 border-b border-gray-50 !important; }
        table.dataTable.stripe tbody tr.odd { @apply bg-gray-50/30; }
        table.dataTable.hover tbody tr:hover { @apply bg-brand/5 !important; }
        .hide-dt-search .dataTables_filter { display: none; }
        /* Console Font for Numbers Platform-Wide */
        .font-mono, 
        .amount, 
        .salary, 
        .price, 
        .balance, 
        .count, 
        .number,
        td.text-right,
        th.text-right {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        },
                        brand: {
                            light: '#e9e6f7',
                            DEFAULT: '#764ba2',
                            dark: '#5a3780',
                        }
                    },
                    borderWidth: {
                        '3': '3px',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧮</text></svg>">
</head>

<body>
    <!-- Center Page Notification -->
    <div id="center-notification"></div>

    <!-- Mobile Navigation Toggle (Tailwind classes) -->
    <div class="lg:hidden fixed top-4 right-4 z-[1001]">
        <button id="sidebarToggle" class="w-12 h-12 bg-black text-white rounded-full shadow-2xl focus:outline-none transform active:scale-95 transition-all flex items-center justify-center border-2 border-white/10">
            <i class="fas fa-bars text-lg" id="toggleIcon"></i>
        </button>
    </div>

    <!-- Production Ready Loading Spinner -->
    <div id="loading-spinner" class="fixed inset-0 bg-white/80 backdrop-blur-sm flex justify-center items-center z-[9999] transition-opacity duration-300">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand"></div>
    </div>
    
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[99] hidden opacity-0 transition-opacity duration-300"></div>

    <div class="wrapper flex min-h-screen bg-gray-50 text-gray-900 font-sans">
        <?php include 'includes/navbar.php'; ?>
        <div class="main-content flex-grow w-full lg:ml-64 transition-all duration-300 p-4 md:p-6 lg:p-8">
            <!-- Toast Container -->
            <div class="fixed top-4 right-4 z-[1100] flex flex-col gap-2">
                <div id="toastContainer"></div>
            </div>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700&display=swap');

        * { font-family: 'Noto Sans Lao', 'Noto Sans', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page-break { page-break-before: always; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">

    <!-- Print toolbar -->
    <div class="no-print fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 px-6 py-3
                flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <a href="javascript:history.back()"
               class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                ກັບຄືນ
            </a>
            <span class="text-gray-300">|</span>
            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
        <button onclick="window.print()"
                class="flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold
                       hover:bg-green-700 active:bg-green-800 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            ພິມ / Save as PDF
        </button>
    </div>

    <!-- Page content -->
    <div class="pt-16 print:pt-0">
        <?= $content ?>
    </div>

</body>
</html>

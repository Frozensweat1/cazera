<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} PDF Export</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            margin: 14mm;
            size: A4;
        }

        body {
            background: #ffffff !important;
            color: #111827 !important;
        }

        .report-print-hidden,
        .report-controls,
        [wire\:loading],
        [wire\:loading\.delay],
        [wire\:loading\.remove] {
            display: none !important;
        }

        .panel,
        .rounded-3xl,
        .rounded-2xl,
        .rounded-xl,
        .rounded-lg {
            break-inside: avoid;
            box-shadow: none !important;
        }

        .panel {
            border: 1px solid #e5e7eb !important;
            background: #ffffff !important;
        }

        table {
            page-break-inside: auto;
        }

        tr,
        img,
        svg {
            break-inside: avoid;
        }

        @media print {
            .export-toolbar {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <main class="mx-auto max-w-7xl p-6 text-gray-900 sm:p-8">
        <div class="export-toolbar mb-6 flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-950">{{ $title }}</h1>
                <p class="text-sm text-gray-500">Generated {{ $generatedAt->format('M d, Y h:i A') }}</p>
            </div>
            <button type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                Print / Save PDF
            </button>
        </div>

        <section class="report-export-content">
            {!! $content !!}
        </section>
    </main>

    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => window.print(), 450);
        });
    </script>
</body>

</html>

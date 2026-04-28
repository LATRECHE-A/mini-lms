{{-- File: resources/views/layouts/error.blade.php --}}
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Erreur' }} — Mini LMS</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#f0f4ff', 100: '#dbe4ff', 200: '#bac8ff', 300: '#91a7ff',
                            400: '#748ffc', 500: '#5c7cfa', 600: '#4c6ef5', 700: '#4263eb',
                            800: '#3b5bdb', 900: '#364fc7',
                        },
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700" rel="stylesheet" />
</head>
<body class="h-full bg-slate-50 font-sans text-slate-700 antialiased">
    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 mb-10">
                <div class="w-9 h-9 rounded-lg bg-brand-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <span class="text-lg font-semibold text-slate-900 tracking-tight">Mini LMS</span>
            </a>

            @yield('content')
        </div>
    </main>
</body>
</html>

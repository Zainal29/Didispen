<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DIDISPEN') - Sistem Informasi Dispensasi</title>
    
    {{-- Tailwind CSS & Font Awesome --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans text-gray-800">
    
    {{-- Main Content Area --}}
    <main class="flex-grow flex items-center justify-center p-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="text-center py-4 text-sm text-gray-500 border-t bg-white">
        &copy; {{ date('Y') }} DIDISPEN. Hak Cipta Dilindungi.
    </footer>

    {{-- Stack untuk JavaScript tambahan --}}
    @stack('scripts')
</body>
</html>
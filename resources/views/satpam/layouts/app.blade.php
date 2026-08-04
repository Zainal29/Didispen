<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Satpam') - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        {{-- SIDEBAR --}}
        <aside class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-5 border-b">
                <h1 class="text-xl font-bold text-red-800">
                    <i class="fas fa-shield-alt mr-2"></i>DIDISPEN
                </h1>
                <p class="text-xs text-gray-500 mt-1">Panel Satpam</p>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
               <a href="{{ route('satpam.dashboard') }}" 
       class="flex items-center px-3 py-2 rounded text-gray-700 {{ request()->routeIs('satpam.dashboard') ? 'bg-red-100 text-red-800 font-semibold' : 'hover:bg-gray-100' }}">
        <i class="fas fa-tachometer-alt w-5 mr-3"></i> Dashboard
    </a>
                <a href="{{ route('satpam.scan') }}" 
       class="flex items-center px-3 py-3 rounded bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg hover:from-red-700 hover:to-red-800 transition {{ request()->routeIs('satpam.scan') ? 'ring-2 ring-red-300' : '' }}">
        <i class="fas fa-qrcode w-5 mr-3 text-lg"></i> 
        <span class="font-bold">Scan QR Code</span>
    </a>
            </nav>

            <div class="p-3 border-t">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-3 py-2 rounded text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm px-6 py-3 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page-title')</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-user-shield mr-1"></i>
                        {{ auth()->user()->name }}
                    </span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                @include('components.alert')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
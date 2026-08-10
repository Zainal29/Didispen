<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DIDISPEN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-700">DIDISPEN</h1>
            <p class="text-gray-600 text-sm mt-1">Sistem Informasi Dispensasi Digital</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="mr-2 rounded text-blue-600 focus:ring-blue-500">
                    Ingat Saya
                </label>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-8 p-4 bg-gray-50 rounded text-xs text-gray-600 border border-gray-200">
            <p class="font-semibold mb-2">Akun Demo (Password: <span class="font-mono text-red-600">password</span>):</p>
            <p class="font-semibold mb-2">Akun Demo (Password satpam: <span class="font-mono text-red-600">satpam123</span>):</p>
            <ul class="space-y-1">
                <li>👨‍💼 Admin: <span class="font-mono">admin@sch.id</span></li>
                <li>👨‍🏫 Guru: <span class="font-mono">budi@sch.id</span></li>
                <li>👨‍🎓 Siswa: <span class="font-mono">ahmad@sch.id</span></li>
                <li>Satpam: <span class="font-mono">satpam@smk.sch.id</span></li>
            </ul>
        </div>
    </div>
</body>
</html>

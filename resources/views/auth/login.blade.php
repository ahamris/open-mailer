<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — CLOM</title>
    @vite(['resources/css/app.css'])
    <style>body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                <span class="text-white font-bold text-xl">C</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">CLOM</h1>
            <p class="text-sm text-gray-500">CodeLabs Open Mailer</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            @if($errors->any())
            <div class="mb-4 px-3 py-2 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/admin/login">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" required>
                    </div>
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2.5 rounded-lg text-sm transition-colors">Sign in</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

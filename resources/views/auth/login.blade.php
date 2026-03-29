<!DOCTYPE html>
<html lang="nl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — CLOM</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="card bg-base-100 shadow-xl w-full max-w-sm">
        <div class="card-body">
            <div class="text-center mb-4">
                <h1 class="text-3xl font-bold">CLOM</h1>
                <p class="text-sm text-base-content/60">CodeLabs Open Mailer</p>
            </div>

            @if($errors->any())
            <div class="alert alert-error text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/admin/login">
                @csrf
                <fieldset class="fieldset">
                    <label class="fieldset-label">Email</label>
                    <input type="email" name="email" class="input input-bordered w-full" value="{{ old('email') }}" required autofocus>
                </fieldset>
                <fieldset class="fieldset mt-3">
                    <label class="fieldset-label">Wachtwoord</label>
                    <input type="password" name="password" class="input input-bordered w-full" required>
                </fieldset>
                <button type="submit" class="btn btn-primary w-full mt-4">Inloggen</button>
            </form>
        </div>
    </div>
</body>
</html>

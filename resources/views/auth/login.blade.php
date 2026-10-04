<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — {{ config('etablissement.sigle') }}</title>
    <link rel="icon" href="{{ asset(config('etablissement.logo')) }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; }
        .brand-gradient { background: linear-gradient(135deg, #065f46 0%, #047857 45%, #059669 100%); }
    </style>
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-screen flex">

        {{-- ==================== Panneau de gauche (branding) ==================== --}}
        <div class="hidden lg:flex lg:w-1/2 brand-gradient relative overflow-hidden">
            <div class="absolute inset-0 opacity-10"
                 style="background-image:radial-gradient(circle at 20% 30%, #fff 1px, transparent 1px);background-size:26px 26px;"></div>
            <div class="relative z-10 flex flex-col justify-between p-14 text-white w-full">
                <div class="flex items-center gap-3">
                    <div class="bg-white/95 rounded-2xl p-2 shadow-lg">
                        <img src="{{ asset(config('etablissement.logo')) }}" alt="Logo" class="h-14 w-auto">
                    </div>
                    <div>
                        <p class="font-bold text-xl leading-tight">{{ config('etablissement.sigle') }}</p>
                        <p class="text-emerald-100 text-sm">La Majestueuse</p>
                    </div>
                </div>

                <div>
                    <h1 class="text-4xl font-bold leading-tight">Bienvenue sur votre<br>espace numérique.</h1>
                    <p class="mt-4 text-emerald-100 text-lg max-w-md">
                        Étudiants, personnel et administration — accédez à vos informations,
                        notes, bulletins et services en un seul endroit.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2 rounded-full text-sm">🎓 Étudiants</span>
                        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2 rounded-full text-sm">👤 Personnel</span>
                        <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2 rounded-full text-sm">💼 RH &amp; Paie</span>
                    </div>
                </div>

                <p class="text-emerald-200 text-sm">© {{ date('Y') }} {{ config('etablissement.sigle') }} — {{ config('etablissement.slogan') }}</p>
            </div>
        </div>

        {{-- ==================== Panneau de droite (formulaire) ==================== --}}
        <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-md">

                {{-- Logo mobile --}}
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    <img src="{{ asset(config('etablissement.logo')) }}" alt="Logo" class="h-16 w-auto">
                    <div>
                        <p class="font-bold text-xl text-emerald-800 leading-tight">{{ config('etablissement.sigle') }}</p>
                        <p class="text-gray-500 text-sm">La Majestueuse</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900">Connexion</h2>
                    <p class="mt-2 text-gray-500">Entrez vos identifiants pour accéder à votre espace.</p>
                </div>

                {{-- Message d'erreur --}}
                @if (session('error'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Message de succès --}}
                @if (session('success'))
                    <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <svg class="w-5 h-5 flex-shrink-0 text-emerald-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Erreurs de validation --}}
                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="login-form" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Adresse email</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                   placeholder="vous@{{ config('etablissement.domaine') }}"
                                   class="w-full pl-10 pr-3 py-3 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        </div>
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Mot de passe</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="w-full pl-10 pr-11 py-3 rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400
                                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <button type="button" onclick="togglePwd()" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                                <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg id="eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Se souvenir --}}
                    <div class="flex items-center justify-between">
                        <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                            <input id="remember" name="remember" type="checkbox"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-gray-600">Se souvenir de moi</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    {{-- Bouton --}}
                    <button type="submit" id="submit-btn"
                            class="w-full flex items-center justify-center gap-2 rounded-xl brand-gradient px-4 py-3 text-white font-semibold
                                   shadow-lg shadow-emerald-600/20 hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2
                                   transition disabled:opacity-60 disabled:cursor-wait">
                        <svg id="spinner" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span id="btn-label">Se connecter</span>
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-gray-500">
                    Pas encore inscrit ?
                    <a href="/preinscription" class="font-medium text-emerald-600 hover:text-emerald-700">Faire une préinscription</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePwd() {
            const input = document.getElementById('password');
            const open = document.getElementById('eye-open');
            const off  = document.getElementById('eye-off');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            open.classList.toggle('hidden', show);
            off.classList.toggle('hidden', !show);
        }
        document.getElementById('login-form').addEventListener('submit', function () {
            document.getElementById('submit-btn').disabled = true;
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btn-label').textContent = 'Connexion en cours…';
        });
    </script>
</body>
</html>

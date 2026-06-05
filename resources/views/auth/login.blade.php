<x-layouts.auth title="Sign in" subtitle="Use your staff credentials to access the backoffice.">
    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-800">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <div class="flex items-center justify-between gap-4 text-sm">
            <label class="inline-flex items-center gap-2 text-slate-600">
                <input type="checkbox" name="remember"
                    class="h-4 w-4 rounded border-slate-300 text-[#17110c] focus:ring-[#b98d36]" />
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-semibold text-[#7a5520] hover:text-[#17110c]">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="auth-primary-button">
            Sign in
        </button>
    </form>
</x-layouts.auth>

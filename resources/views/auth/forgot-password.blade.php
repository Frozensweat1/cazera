<x-layouts.auth title="Reset access" subtitle="Enter your email and we will send a secure password reset link.">
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

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-800">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <button type="submit" class="auth-primary-button">
            Email reset link
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">
        Remembered your password?
        <a href="{{ route('login') }}" class="font-bold text-slate-950 hover:text-[#7a5520]">Sign in</a>
    </p>
</x-layouts.auth>

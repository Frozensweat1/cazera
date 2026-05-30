<x-layouts.auth title="Confirm password" subtitle="Confirm your password before continuing to this protected area.">
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <label for="password" class="mb-2 block text-sm font-semibold text-slate-800">Password</label>
            <input id="password" type="password" name="password" required autofocus autocomplete="current-password"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <button type="submit" class="auth-primary-button">
            Confirm password
        </button>
    </form>
</x-layouts.auth>

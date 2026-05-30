<x-layouts.auth title="Two-factor verification" subtitle="Enter an authenticator code or one of your recovery codes.">
    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="code" class="mb-2 block text-sm font-semibold text-slate-800">Authentication code</label>
            <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <div>
            <label for="recovery_code" class="mb-2 block text-sm font-semibold text-slate-800">Recovery code</label>
            <input id="recovery_code" type="text" name="recovery_code" autocomplete="one-time-code"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-950 outline-none transition focus:border-[#b98d36] focus:bg-white focus:ring-4 focus:ring-[#d7b56d]/20" />
        </div>

        <button type="submit" class="auth-primary-button">
            Verify access
        </button>
    </form>
</x-layouts.auth>

<x-layouts.auth title="Registration disabled" subtitle="Staff accounts are created and assigned from the backoffice by authorized administrators.">
    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
        Public account registration is not available for this system. Please contact your administrator if you need
        access.
    </div>

    <p class="mt-7 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-bold text-slate-950 hover:text-[#7a5520]">Sign in</a>
    </p>
</x-layouts.auth>

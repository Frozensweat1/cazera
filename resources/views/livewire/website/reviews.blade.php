<div>
    <x-website.breadcrumbs :items="[['label' => 'Testimonials & Reviews']]" />

    <section class="luxury-container pb-16 pt-10">
        <x-website.section-heading :eyebrow="\App\Support\WebsiteContent::copy('reviews.eyebrow', $page['eyebrow'] ?? 'Testimonials & reviews')" :title="\App\Support\WebsiteContent::copy('reviews.title', $page['title'] ?? 'Real guest confidence, presented with restraint.')" :subtitle="\App\Support\WebsiteContent::copy('reviews.subtitle', $page['subtitle'] ?? 'Browse published reviews, filter by branch or menu item, and leave a new review for moderation.')" />

        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($testimonials->take(3) as $testimonial)
                <x-website.testimonial-card :testimonial="$testimonial" />
            @endforeach
        </div>
    </section>

    <section class="luxury-container pb-20">
        <div class="grid gap-8 lg:grid-cols-[0.75fr_1.25fr]">
            <aside class="space-y-6">
                <div class="rounded-[1.5rem] border border-ivory/10 bg-charcoal p-6">
                    <h2 class="font-serif text-3xl font-semibold text-ivory">Filter reviews</h2>
                    <div class="mt-5 grid gap-4">
                        <input wire:model.live.debounce.300ms="search" type="search" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Search reviews">
                        <select wire:model.live="branch_id" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            <option value="">All branches</option>
                            @foreach ($branches->whereNotNull('id') as $branch)
                                <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="menu_item_id" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            <option value="">All menu items</option>
                            @foreach ($menuItems as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-ivory/10 bg-charcoal p-6">
                    <h2 class="font-serif text-3xl font-semibold text-ivory">Leave a review</h2>
                    @if ($successMessage)
                        <div class="mt-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm text-emerald-100">{{ $successMessage }}</div>
                    @endif
                    <form wire:submit.prevent="submitReview" class="mt-5 grid gap-4">
                        <input wire:model.defer="reviewer_name" type="text" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Your name">
                        @error('reviewer_name')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <input wire:model.defer="email" type="email" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Email optional">
                        @error('email')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <select wire:model.defer="rating" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                        @error('rating')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        @error('menu_item_id')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <textarea wire:model.defer="review" rows="5" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Tell us about your visit"></textarea>
                        @error('review')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <x-website.button type="submit" wire:loading.attr="disabled" wire:target="submitReview">
                            <span wire:loading.remove wire:target="submitReview">Submit Review</span>
                            <span wire:loading wire:target="submitReview">Submitting...</span>
                        </x-website.button>
                    </form>
                </div>

                <div class="rounded-[1.5rem] border border-ivory/10 bg-ivory/[0.04] p-6">
                    <h2 class="font-serif text-3xl font-semibold text-ivory">Share a testimonial</h2>
                    @if ($testimonialSuccessMessage)
                        <div class="mt-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-sm text-emerald-100">{{ $testimonialSuccessMessage }}</div>
                    @endif
                    <form wire:submit.prevent="submitTestimonial" class="mt-5 grid gap-4">
                        <input wire:model.defer="testimonial_author_name" type="text" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Your name">
                        @error('testimonial_author_name')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <input wire:model.defer="testimonial_title" type="text" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="Title optional">
                        @error('testimonial_title')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <select wire:model.defer="testimonial_branch_id" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            <option value="">General testimonial</option>
                            @foreach ($branches->whereNotNull('id') as $branch)
                                <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                            @endforeach
                        </select>
                        @error('testimonial_branch_id')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <select wire:model.defer="testimonial_rating" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                        @error('testimonial_rating')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <textarea wire:model.defer="testimonial_quote" rows="5" class="rounded-2xl border border-ivory/10 bg-ink/65 px-4 py-3 text-ivory outline-none focus:border-gold/70" placeholder="What should future guests know?"></textarea>
                        @error('testimonial_quote')<span class="text-sm text-red-300">{{ $message }}</span>@enderror
                        <x-website.button type="submit" wire:loading.attr="disabled" wire:target="submitTestimonial">
                            <span wire:loading.remove wire:target="submitTestimonial">Submit Testimonial</span>
                            <span wire:loading wire:target="submitTestimonial">Submitting...</span>
                        </x-website.button>
                    </form>
                </div>
            </aside>

            <div class="space-y-5">
                @forelse ($reviews as $reviewItem)
                    <article class="rounded-[1.5rem] border border-ivory/10 bg-ivory/[0.04] p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <h3 class="font-serif text-3xl font-semibold text-ivory">{{ $reviewItem->reviewer_name }}</h3>
                                <p class="mt-1 text-sm text-parchment/60">{{ $reviewItem->menuItem?->name ?? 'Guest experience' }}</p>
                            </div>
                            <p class="text-gold" aria-label="{{ $reviewItem->rating }} out of 5 stars">{!! str_repeat('&starf;', $reviewItem->rating) !!}</p>
                        </div>
                        <p class="mt-4 leading-8 text-parchment/76">{{ $reviewItem->review ?? 'A memorable experience.' }}</p>
                    </article>
                @empty
                    <div class="rounded-[1.5rem] border border-ivory/10 p-8 text-parchment/70">No approved reviews match this filter yet.</div>
                @endforelse

                <div class="text-ivory">{{ $reviews->links() }}</div>
            </div>
        </div>
    </section>
</div>

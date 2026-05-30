<?php

namespace App\Livewire\Backoffice\Pos;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Sale;
use App\Models\SaleItem;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class KitchenIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';
    public $showCompleted = false;

    public function render()
    {
        $branchId = session('branch_id');

        $saleItems = SaleItem::with(['sale.customer', 'menuItem'])
            ->accessible()
            ->where('is_kitchen_notified', true)
            ->when(!$this->showCompleted, fn($query) => $query->where('kitchen_status', '!=', 'completed'))
            ->when($branchId, fn($query) => $query->where('branch_id', $branchId))
            ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
            ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
            ->when($this->filterStatus, fn($query) => $query->where('kitchen_status', $this->filterStatus))
            ->oldest('created_at')
            ->paginate(20);

        return view('livewire.backoffice.pos.kitchen-index', [
            'saleItems' => $saleItems,
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: $branchId ?: null),
        ]);
    }

    public function updatedFilterBranch(): void
    {
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedFilterModule(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedShowCompleted(): void
    {
        $this->resetPage();
    }

    public function markAs(int $saleItemId, string $status)
    {
        $allowed = ['queued', 'cooking', 'ready', 'completed'];

        if (! in_array($status, $allowed, true)) {
            return;
        }

        $item = SaleItem::accessible()->findOrFail($saleItemId);

        $item->update([
            'kitchen_status' => $status,
            'kitchen_started_at' => in_array($status, ['cooking', 'ready', 'completed'], true)
                ? $item->kitchen_started_at ?? now()
                : $item->kitchen_started_at,
            'kitchen_completed_at' => $status === 'completed'
                ? now()
                : $item->kitchen_completed_at,
        ]);

        $this->updateSaleKitchenStatus($item->sale);

        LivewireAlert::title('Kitchen Updated')
            ->text('Kitchen status updated to ' . ucfirst($status) . '.')
            ->success()
            ->show();
    }

    protected function updateSaleKitchenStatus(Sale $sale): void
    {
        $statuses = $sale->items()->pluck('kitchen_status')->unique()->toArray();

        if (! $sale->items()->exists()) {
            return;
        }

        if (count($statuses) === 1 && $statuses[0] === 'completed') {
            $sale->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return;
        }

        if (in_array('cooking', $statuses, true)) {
            $sale->update(['status' => 'cooking']);

            return;
        }

        if (in_array('ready', $statuses, true)) {
            $sale->update(['status' => 'ready']);

            return;
        }

        if (in_array('queued', $statuses, true)) {
            $sale->update(['status' => 'confirmed']);
        }
    }

    public function formatWaitTime(SaleItem $item): string
    {
        $seconds = max(0, (int) $item->created_at?->diffInSeconds(now()));
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return $hours > 0
            ? sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds)
            : sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    public function menuItemImageUrl(SaleItem $item): ?string
    {
        $path = trim((string) $item->menuItem?->image_url);

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, ['/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}

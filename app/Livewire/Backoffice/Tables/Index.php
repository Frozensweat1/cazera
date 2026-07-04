<?php

namespace App\Livewire\Backoffice\Tables;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Branch;
use App\Models\DiningTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';

    public $tableId;
    public $branch_id = null;
    public $name;
    public $seats;
    public $status = 'available';

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tables', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $this->branch_id))
                    ->ignore($this->tableId),
            ],
            'seats' => ['nullable', 'integer', 'min:1', 'max:999'],
            'status' => ['required', Rule::in(['available', 'occupied', 'reserved'])],
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.tables.index', [
            'tables' => DiningTable::with('branch')
                ->accessible()
                ->when($this->filterBranch, fn ($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->search, fn ($query) => $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%")
                        ->orWhere('status', 'like', "%{$this->search}%");
                }))
                ->orderBy('name')
                ->paginate(12),
            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterBranch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'tableId',
            'branch_id',
            'name',
            'seats',
            'status',
        ]);

        $this->branch_id = session('branch_id') ?: '';
        $this->status = 'available';
        $this->resetValidation();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', 'table-form');
    }

    public function edit($id): void
    {
        try {
            $table = DiningTable::accessible()->findOrFail($id);

            $this->tableId = $table->id;
            $this->branch_id = $table->branch_id;
            $this->name = $table->name;
            $this->seats = $table->seats;
            $this->status = $table->status;

            $this->resetValidation();
            $this->dispatch('open-modal', 'table-form');
        } catch (Throwable $e) {
            Log::error('TablesIndex::edit failed', ['exception' => $e, 'table_id' => $id]);

            LivewireAlert::title('Unable to Load Table')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }

    public function save(): void
    {
        try {
            $data = $this->validate();
            $this->authorizeBranch($data['branch_id']);

            DB::transaction(function () use ($data) {
                DiningTable::updateOrCreate([
                    'id' => $this->tableId,
                ], [
                    'branch_id' => $data['branch_id'],
                    'name' => trim($data['name']),
                    'slug' => Str::slug($data['name']),
                    'seats' => $data['seats'] ?: null,
                    'status' => $data['status'],
                ]);
            });

            $this->dispatch('close-modal', 'table-form');

            LivewireAlert::title($this->tableId ? 'Table Updated' : 'Table Created')
                ->text('Dining table saved successfully.')
                ->success()
                ->show();

            $this->resetForm();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('TablesIndex::save failed', ['exception' => $e, 'table_id' => $this->tableId]);

            LivewireAlert::title('Unable to Save Table')
                ->text('Please check the table details and try again.')
                ->error()
                ->show();
        }
    }

    public function confirmDelete($id): void
    {
        LivewireAlert::title('Delete Table')
            ->text('Are you sure you want to delete this dining table?')
            ->asConfirm()
            ->onConfirm('delete', ['id' => $id])
            ->show();
    }

    public function delete($id): void
    {
        try {
            $id = is_array($id) ? $id['id'] : $id;

            DB::transaction(function () use ($id) {
                $table = DiningTable::accessible()->findOrFail($id);

                abort_if($table->status === 'occupied', 422, 'Occupied tables cannot be deleted.');

                $table->delete();
            });

            LivewireAlert::title('Table Deleted')
                ->text('Dining table deleted successfully.')
                ->success()
                ->show();
        } catch (Throwable $e) {
            Log::error('TablesIndex::delete failed', ['exception' => $e, 'table_id' => $id]);

            LivewireAlert::title('Unable to Delete Table')
                ->text($e->getMessage() ?: 'Please try again.')
                ->error()
                ->show();
        }
    }

    public function release($id): void
    {
        try {
            DB::transaction(function () use ($id) {
                DiningTable::accessible()
                    ->findOrFail($id)
                    ->update(['status' => 'available']);
            });

            LivewireAlert::title('Table Released')
                ->text('The table is now available.')
                ->success()
                ->show();
        } catch (Throwable $e) {
            Log::error('TablesIndex::release failed', ['exception' => $e, 'table_id' => $id]);

            LivewireAlert::title('Unable to Release Table')
                ->text('Please refresh and try again.')
                ->error()
                ->show();
        }
    }
}

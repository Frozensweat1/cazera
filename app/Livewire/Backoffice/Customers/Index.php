<?php

namespace App\Livewire\Backoffice\Customers;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Index extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';

    public $customerId;

    public $branch_id;

    public $name;
    public $email;
    public $phone;
    public $address;

    public $latitude;
    public $longitude;

    public $customer_type = 'regular';

    public $status = 'active';

    protected function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',

            'name' => 'required|string|max:255',

            'email' => [
                'nullable',
                'email',
                Rule::unique('customers', 'email')->ignore($this->customerId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'phone')->ignore($this->customerId),
            ],

            'address' => 'nullable|string',

            'latitude' => 'nullable|numeric',

            'longitude' => 'nullable|numeric',

            'customer_type' => 'required|in:regular,vip,corporate',

            'status' => 'required|in:active,inactive,banned',
        ];
    }

    public function render()
    {
        return view('livewire.backoffice.customers.index', [
            'customers' => Customer::with('branch')
                ->accessible()
                ->when(
                    $this->search,
                    fn($query) =>
                    $query->where(fn ($query) => $query
                        ->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%"))
                )
                ->latest()
                ->paginate(10),

            'branches' => $this->accessibleBranches(),
        ]);
    }

    public function resetForm()
    {
        $this->reset([
            'customerId',
            'branch_id',
            'name',
            'email',
            'phone',
            'address',
            'latitude',
            'longitude',
        ]);

        $this->customer_type = 'regular';

        $this->status = 'active';
    }

    public function create()
    {
        $this->resetForm();

        $this->dispatch('open-modal', 'customer-form');
    }

    public function edit($id)
    {
        $customer = Customer::accessible()->findOrFail($id);

        $this->customerId = $customer->id;

        $this->branch_id = $customer->branch_id;

        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;

        $this->latitude = $customer->latitude;
        $this->longitude = $customer->longitude;

        $this->customer_type = $customer->customer_type;

        $this->status = $customer->status;

        $this->dispatch('open-modal', 'customer-form');
    }

    public function save()
    {
        $this->validate();
        $this->authorizeBranch($this->branch_id);

        Customer::updateOrCreate(
            ['id' => $this->customerId],
            [
                'branch_id' => $this->branch_id,

                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,

                'latitude' => $this->latitude,
                'longitude' => $this->longitude,

                'customer_type' => $this->customer_type,

                'status' => $this->status,
            ]
        );

        $this->dispatch('close-modal', 'customer-form');

        LivewireAlert::title('Customer Saved')
            ->text('Customer record saved successfully.')
            ->success()
            ->show();

        $this->resetForm();
    }

    public function delete($id)
    {
        LivewireAlert::title('Delete Customer')
            ->text('Are you sure you want to delete this customer?')
            ->asConfirm()
            ->onConfirm('performDelete', ['id' => $id])
            ->show();
    }

    public function performDelete(array $data)
    {
        $id = $data['id'];

        Customer::accessible()->findOrFail($id)->delete();

        LivewireAlert::title('Customer Deleted')
            ->text('Customer deleted successfully.')
            ->success()
            ->show();
    }
}

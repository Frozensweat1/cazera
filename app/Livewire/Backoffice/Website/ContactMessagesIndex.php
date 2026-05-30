<?php

namespace App\Livewire\Backoffice\Website;

use App\Livewire\Concerns\HasBranchScope;
use App\Models\ContactMessage;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class ContactMessagesIndex extends Component
{
    use WithPagination;
    use HasBranchScope;

    public $search = '';
    public $filterBranch = '';
    public $filterModule = '';
    public $filterStatus = '';

    public $selectedMessageId;
    public $selectedMessage;

    public function render()
    {
        return view('livewire.backoffice.website.contact-messages-index', [
            'messages' => ContactMessage::with(['branch', 'module', 'responder'])
                ->accessible()
                ->when($this->filterBranch, fn($query) => $query->where('branch_id', $this->filterBranch))
                ->when($this->filterModule, fn($query) => $query->where('module_id', $this->filterModule))
                ->when($this->filterStatus, fn($query) => $query->where('status', $this->filterStatus))
                ->when($this->search, fn($query) => $query->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('subject', 'like', "%{$this->search}%")
                        ->orWhere('message', 'like', "%{$this->search}%");
                }))
                ->latest('received_at')
                ->paginate(15),
            'branches' => $this->accessibleBranches(),
            'modules' => $this->accessibleModules($this->filterBranch ?: null),
        ]);
    }

    public function showMessage($id)
    {
        $this->selectedMessageId = $id;
        $this->selectedMessage = ContactMessage::accessible()->findOrFail($id);
        $this->dispatch('open-modal', 'contact-message-detail');
    }

    public function quickMarkAs(int $id, string $status): void
    {
        $message = ContactMessage::accessible()->findOrFail($id);
        $message->update([
            'status' => $status,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        LivewireAlert::title('Message Updated')
            ->text('Contact message status updated to ' . ucfirst($status) . '.')
            ->success()
            ->show();
    }

    public function markAs($status)
    {
        if (! $this->selectedMessageId || ! $this->selectedMessage) {
            return;
        }

        $message = $this->selectedMessage;
        $message->update([
            'status' => $status,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        LivewireAlert::title('Message Updated')
            ->text('Contact message status updated to ' . ucfirst($status) . '.')
            ->success()
            ->show();

        $this->dispatch('close-modal', 'contact-message-detail');
    }
}

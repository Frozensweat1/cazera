<?php

namespace App\Livewire\Backoffice {
    use Livewire\Component;

    class ForbiddenActionFixture extends Component
    {
        public function forbiddenAction(): void
        {
            abort(403);
        }

        public function render(): string
        {
            return '<div>Action response completed</div>';
        }
    }
}

namespace Tests\Feature {
    use App\Livewire\Backoffice\ForbiddenActionFixture;
    use Livewire\Livewire;
    use Tests\TestCase;

    class LivewireForbiddenActionTest extends TestCase
    {
        public function test_backoffice_forbidden_action_is_handled_without_forbidden_response(): void
        {
            Livewire::test(ForbiddenActionFixture::class)
                ->call('forbiddenAction')
                ->assertSee('Action response completed');
        }
    }
}

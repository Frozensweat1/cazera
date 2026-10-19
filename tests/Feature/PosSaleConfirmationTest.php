<?php

namespace App\Livewire\Backoffice\Pos {
    use App\Livewire\Backoffice\Pos\Index;

    class SaleConfirmationFixture extends Index
    {
        public bool $saleProcessed = false;

        protected function processSale(): void
        {
            $this->saleProcessed = true;
        }

        public function render(): string
        {
            return '<div>POS sale confirmation test</div>';
        }
    }
}

namespace Tests\Feature {
    use App\Livewire\Backoffice\Pos\SaleConfirmationFixture;
    use Livewire\Livewire;
    use Tests\TestCase;

    class PosSaleConfirmationTest extends TestCase
    {
        public function test_credit_sale_processes_without_confirmation(): void
        {
            Livewire::test(SaleConfirmationFixture::class)
                ->set('splitPayments', [['method' => 'credit_sale', 'amount' => 0]])
                ->call('saveSale')
                ->assertSet('saleProcessed', true);
        }

        public function test_non_credit_payment_waits_for_confirmation(): void
        {
            Livewire::test(SaleConfirmationFixture::class)
                ->set('splitPayments', [['method' => 'cash', 'amount' => 25]])
                ->call('saveSale')
                ->assertSet('saleProcessed', false)
                ->call('confirmSale')
                ->assertSet('saleProcessed', true);
        }

        public function test_mixed_credit_and_card_payment_waits_for_confirmation(): void
        {
            Livewire::test(SaleConfirmationFixture::class)
                ->set('splitPayments', [
                    ['method' => 'credit_sale', 'amount' => 0],
                    ['method' => 'card', 'amount' => 25],
                ])
                ->call('saveSale')
                ->assertSet('saleProcessed', false);
        }
    }
}

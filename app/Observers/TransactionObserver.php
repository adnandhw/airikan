<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Stock deduction logic has been moved to App\Filament\Resources\Transactions\Pages\EditTransaction::handleRecordUpdate
        // to ensure it runs correctly within the Filament Admin Panel context and supports MySQL.
        // This Observer is intentionally left empty to prevent double-deduction and MySQL errors (unknown column _id).
    }
}

<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('transactions.print', $record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only update payment_proof if a new file is uploaded
        if (isset($data['new_payment_proof']) && !empty($data['new_payment_proof'])) {
            // Using first element if array (Filament handles upload as array/string based on config)
             $proof = is_array($data['new_payment_proof']) 
                ? array_shift($data['new_payment_proof']) 
                : $data['new_payment_proof'];
            
            // If it's a temp file object, Filament handles storage automatically if dehydrated is true.
            // But we set dehydrated(false) to handle manually if needed, OR we can let Filament handle it?
            // Actually, because we set dehydrated(false), $data['new_payment_proof'] might be the temp key.
            // 
            // EASIER APPROACH: Let Filament handle the upload normally but map it to 'payment_proof'
            $data['payment_proof'] = $proof;
        }

        // Remove the temporary field key so it doesn't try to save to non-existent column
        unset($data['new_payment_proof']);

        return $data;
    }
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $record->update($data);
        $record->refresh(); // Ensure we have the latest state (including is_stock_deducted)

        // Decrease stock ONLY if status is 'approve' AND stock has NOT been deducted yet
        if ($record->status === 'approve' && !$record->is_stock_deducted) {
            $this->updateStock($record, 'decrease');
            
            // Mark as deducted so it never decreases again for this transaction
            $record->is_stock_deducted = true;
            $record->save();
        }

        // NOTE: User requested "stok tidak menambah" (Stock does not increase).
        // Therefore, we removed the logic to restore stock (refund) when switching away from 'approve'.
        // This prevents stock increase issues but means cancellations do not automatically return stock.

        return $record;
    }

    protected function updateStock($record, $action)
    {
        $products = $record->products ?? [];
        if (is_string($products)) {
            $products = json_decode($products, true);
        }

        foreach ($products as $item) {
            $qty = (int) $item['quantity'];
            $productId = $item['product_id'];

            // 1. Try to find in Regular Products
            $mainProduct = \App\Models\Product::find($productId);
            
            if ($mainProduct) {
                if ($action === 'decrease') {
                    if ($mainProduct->stock >= $qty) $mainProduct->decrement('stock', $qty);
                    \App\Models\ProductReseller::where('product_id', $mainProduct->id)->decrement('stock', $qty);
                } else {
                    $mainProduct->increment('stock', $qty);
                    \App\Models\ProductReseller::where('product_id', $mainProduct->id)->increment('stock', $qty);
                }

            } else {
                // 2. Try to find in Reseller Products
                $resellerProduct = \App\Models\ProductReseller::find($productId);

                if ($resellerProduct) {
                    if ($action === 'decrease') {
                        if ($resellerProduct->stock >= $qty) $resellerProduct->decrement('stock', $qty);
                    } else {
                        $resellerProduct->increment('stock', $qty);
                    }

                    // Handle Parent Product
                    if (!empty($resellerProduct->product_id)) {
                        $parentProduct = \App\Models\Product::find($resellerProduct->product_id);
                        if ($parentProduct) {
                            if ($action === 'decrease') {
                                if ($parentProduct->stock >= $qty) $parentProduct->decrement('stock', $qty);
                                // Siblings
                                \App\Models\ProductReseller::where('product_id', $parentProduct->id)
                                    ->where('id', '!=', $resellerProduct->id)
                                    ->decrement('stock', $qty);
                            } else {
                                $parentProduct->increment('stock', $qty);
                                // Siblings
                                \App\Models\ProductReseller::where('product_id', $parentProduct->id)
                                    ->where('id', '!=', $resellerProduct->id)
                                    ->increment('stock', $qty);
                            }
                        }
                    }
                }
            }
        }
    }
}

<?php

namespace App\Filament\Resources\Buyers\Pages;

use App\Filament\Resources\Buyers\BuyerResource;
use Filament\Resources\Pages\EditRecord;

class EditBuyer extends EditRecord
{
    protected static string $resource = BuyerResource::class;
    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if ($record->wasChanged('reseller_status') && $record->reseller_status === 'approved') {
            $phone = $record->phone;
            // Normalize phone (replace leading 0 with 62)
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $message = "Halo {$record->name}, Pengajuan Reseller Anda telah DISETUJUI oleh Admin Air Ikan Store. Sekarang Anda dapat menikmati harga khusus reseller. Silakan login kembali pada aplikasi/website.";
            $url = "https://wa.me/{$phone}?text=" . urlencode($message);

            \Filament\Notifications\Notification::make()
                ->title('Reseller Approved')
                ->body('Status updated to Approved. Send notification to user?')
                ->success()
                ->persistent() 
                ->actions([
                    \Filament\Actions\Action::make('send_whatsapp')
                        ->label('Kirim WhatsApp')
                        ->url($url)
                        ->openUrlInNewTab()
                        ->button(),
                ])
                ->send();
        }
    }
}

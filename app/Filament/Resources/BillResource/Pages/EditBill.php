<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {
                    // Delete associated records
                    $record->billProducts()->delete();
                    $record->vendorTransaction()->delete();

                    // Update vendor balance
                    $record->vendor->decrement('balance', $record->total_amount +
                        ($record->extra_amount_1 ?? 0) +
                        ($record->extra_amount_2 ?? 0) +
                        ($record->extra_amount_3 ?? 0));
                }),
        ];
    }
}

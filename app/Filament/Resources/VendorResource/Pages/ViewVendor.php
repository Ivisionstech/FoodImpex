<?php

namespace App\Filament\Resources\VendorResource\Pages;

use App\Filament\Resources\VendorResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewVendor extends ViewRecord
{
    protected static string $resource = VendorResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Vendor Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('profile')
                            ->circular()
                            ->defaultImageUrl(url('/images/default-profile.png')),
                        Infolists\Components\TextEntry::make('company_name')
                            ->label('Company Name'),
                        Infolists\Components\TextEntry::make('person_name')
                            ->label('Contact Person'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('phone'),
                        Infolists\Components\TextEntry::make('address'),
                        Infolists\Components\TextEntry::make('balance')
                            ->money('PKR')
                            ->color(fn($state) => $state > 0 ? 'danger' : 'success'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(), // ensures full width
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ViewVendor\Widgets\VendorBillsTable::make(['record' => $this->getRecord()]),
            ViewVendor\Widgets\VendorTransactionsTable::make(['record' => $this->getRecord()]),
        ];
    }
}

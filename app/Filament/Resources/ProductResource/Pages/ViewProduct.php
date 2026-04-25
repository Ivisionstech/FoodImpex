<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Product Information')
                    ->schema([
                        Infolists\Components\ImageEntry::make('image')
                            ->circular(),
                        Infolists\Components\TextEntry::make('vendor.company_name')
                            ->label('Vendor'),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpan(2),
                        Infolists\Components\TextEntry::make('purchase_price')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('sale_price')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('stock')
                            ->numeric(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }
}

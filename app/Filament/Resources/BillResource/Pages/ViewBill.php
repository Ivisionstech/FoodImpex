<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Bill Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('vendor.company_name')
                            ->label('Vendor'),
                        Infolists\Components\TextEntry::make('date')
                            ->date(),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('extra_amount_1')
                            ->label('Extra Charge 1')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('extra_amount_2')
                            ->label('Extra Charge 2')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('extra_amount_3')
                            ->label('Extra Charge 3')
                            ->money('PKR'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->getRecord()->billProducts()->getQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.description')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('product.image')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.purchase_price')
                    ->money('PKR')
                    ->label('Purchase Price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.sale_price')
                    ->money('PKR')
                    ->label('Sale Price')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ViewBill\Widgets\BillProductsTable::class,
        ];
    }
}

<?php

namespace App\Filament\Resources\BillResource\Pages\ViewBill\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\BillResource\Pages\ViewBill;

class BillProductsTable extends BaseWidget
{
    protected ViewBill $livewire;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->livewire->getRecord()->billProducts()->getQuery()
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

    protected function getTableHeading(): string
    {
        return 'Products';
    }
}

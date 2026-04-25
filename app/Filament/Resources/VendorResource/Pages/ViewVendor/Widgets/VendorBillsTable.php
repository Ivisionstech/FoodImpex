<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorResource\Pages\ViewVendor\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\VendorResource;
use App\Filament\Resources\BillResource;
use App\Models\Vendor;

class VendorBillsTable extends BaseWidget
{
    public ?Vendor $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record->bills()->getQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('extra_amount_1')
                    ->money('PKR')
                    ->label('Extra 1')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('extra_amount_2')
                    ->money('PKR')
                    ->label('Extra 2')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('extra_amount_3')
                    ->money('PKR')
                    ->label('Extra 3')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->url(fn($record) => VendorResource::getUrl('view', ['record' => $record])),
            ]);
        // ->headerActions([
        //     Tables\Actions\Action::make('add_bill')
        //         ->label('Add Bill')
        //         ->url(fn() => VendorResource::getUrl('add_bill', ['record' => $this->record]))
        //         ->icon('heroicon-o-document-plus'),
        // ]);
    }

    protected function getTableHeading(): string
    {
        return 'Bills';
    }
}

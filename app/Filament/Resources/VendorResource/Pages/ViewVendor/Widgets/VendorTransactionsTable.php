<?php

namespace App\Filament\Resources\VendorResource\Pages\ViewVendor\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Vendor;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class VendorTransactionsTable extends BaseWidget
{
    public ?Vendor $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record->vendorTransactions()->getQuery()
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bill' => 'danger',
                        'payment' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->money('PKR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bill_id')
                    ->label('Bill')
                    ->formatStateUsing(fn($state, $record) => $record && $record->type === 'bill' ? "bill-{$state}" : null)
                    ->url(fn($record) => $record && $record->type === 'bill' ? route('filament.resources.bills.view', ['record' => $record->bill_id]) : null)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record && $record->type === 'bill'),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Bill Details')
                    ->modalContent(fn($record) => view('filament.modals.bill-details', [
                        'bill' => $record,
                        'products' => $record->billProducts,
                        'vendor' => $record->vendor,
                    ]))
                    ->modalWidth('xl'),
            ]);
    }

    protected function getTableHeading(): string
    {
        return 'Transactions';
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Bills';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bill Information')
                    ->schema([
                        Forms\Components\Select::make('vendor_id')
                            ->relationship('vendor', 'company_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('date')
                            ->required()
                            ->maxDate(now()),
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('PKR'),
                        Forms\Components\TextInput::make('extra_amount_1')
                            ->label('Extra Charge 1')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0),
                        Forms\Components\TextInput::make('extra_amount_2')
                            ->label('Extra Charge 2')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0),
                        Forms\Components\TextInput::make('extra_amount_3')
                            ->label('Extra Charge 3')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vendor.company_name')
                    ->searchable()
                    ->sortable(),
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
            ->filters([
                Tables\Filters\SelectFilter::make('vendor')
                    ->relationship('vendor', 'company_name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Bill $record) {
                            // Delete associated records
                            $record->billProducts()->delete();
                            $record->vendorTransaction()->delete();

                            // Update vendor balance
                            $record->vendor->decrement('balance', $record->total_amount +
                                ($record->extra_amount_1 ?? 0) +
                                ($record->extra_amount_2 ?? 0) +
                                ($record->extra_amount_3 ?? 0));
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                // Delete associated records
                                $record->billProducts()->delete();
                                $record->vendorTransaction()->delete();

                                // Update vendor balance
                                $record->vendor->decrement('balance', $record->total_amount +
                                    ($record->extra_amount_1 ?? 0) +
                                    ($record->extra_amount_2 ?? 0) +
                                    ($record->extra_amount_3 ?? 0));
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'view' => Pages\ViewBill::route('/{record}'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}

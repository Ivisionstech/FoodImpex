<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Vendors';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\FileUpload::make('profile')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('200')
                            ->imageResizeTargetHeight('200')
                            ->directory('vendor-profiles')
                            ->visibility('public')
                            ->columnSpan(2)
                            ->helperText('Upload a square profile picture (recommended size: 200x200 pixels)')
                            ->downloadable(),
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('person_name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->maxLength(1000)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('balance')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-profile.png'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('person_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('balance')
                    ->money('PKR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('has_balance')
                    ->options([
                        'with_balance' => 'With Balance',
                        'without_balance' => 'Without Balance',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'with_balance' => $query->where('balance', '>', 0),
                            'without_balance' => $query->where('balance', '<=', 0),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('add_bill')
                        ->label('Add Bill')
                        ->icon('heroicon-o-document-plus')
                        ->form([
                            Section::make('Bill Information')
                                ->schema([
                                    DatePicker::make('date')
                                        ->required()
                                        ->default(now())
                                        ->maxDate(now()),
                                    TextInput::make('amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->default(0),
                                    TextInput::make('extra1')
                                        ->label('Extra Charge 1')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->default(0),
                                    TextInput::make('extra2')
                                        ->label('Extra Charge 2')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->default(0),
                                    TextInput::make('extra3')
                                        ->label('Extra Charge 3')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->default(0),
                                ])->columns(2),

                            Section::make('Products')
                                ->schema([
                                    Repeater::make('products')
                                        ->schema([
                                            TextInput::make('name')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('description')
                                                ->maxLength(1000),
                                            FileUpload::make('image')
                                                ->image()
                                                ->directory('products')
                                                ->visibility('public'),
                                            TextInput::make('purchase_price')
                                                ->required()
                                                ->numeric()
                                                ->prefix('PKR')
                                                ->default(0),
                                            TextInput::make('sale_price')
                                                ->required()
                                                ->numeric()
                                                ->prefix('PKR')
                                                ->default(0),
                                            TextInput::make('stock')
                                                ->required()
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(1)
                                        ->reorderable(false)
                                        ->addActionLabel('Add Product')
                                        ->itemLabel(fn(array $state): ?string => $state['name'] ?? null),
                                ]),
                        ])
                        ->action(function (array $data, $record): void {
                            DB::transaction(function () use ($data, $record) {
                                // Create Bill
                                $bill = $record->bills()->create([
                                    'uuid' => (string) Str::uuid(),
                                    'date' => $data['date'],
                                    'total_amount' => $data['amount'],
                                    'extra_amount_1' => $data['extra1'] ?? 0,
                                    'extra_amount_2' => $data['extra2'] ?? 0,
                                    'extra_amount_3' => $data['extra3'] ?? 0,
                                ]);

                                // Create Products and Bill Products
                                foreach ($data['products'] as $productData) {
                                    // Create Product
                                    $product = $record->products()->create([
                                        'uuid' => (string) Str::uuid(),
                                        'name' => $productData['name'],
                                        'description' => $productData['description'],
                                        'image' => $productData['image'],
                                        'purchase_price' => $productData['purchase_price'],
                                        'sale_price' => $productData['sale_price'],
                                        'stock' => $productData['stock'],
                                    ]);

                                    // Create Bill Product
                                    $bill->billProducts()->create([
                                        'uuid' => (string) Str::uuid(),
                                        'product_id' => $product->id,
                                        'quantity' => $productData['stock'],
                                        'price' => $productData['purchase_price'],
                                    ]);
                                }

                                // Calculate total amount including extra charges
                                $totalAmount = $data['amount'] +
                                    ($data['extra1'] ?? 0) +
                                    ($data['extra2'] ?? 0) +
                                    ($data['extra3'] ?? 0);

                                // Create Vendor Transaction
                                $record->vendorTransactions()->create([
                                    'uuid' => (string) Str::uuid(),
                                    'bill_id' => $bill->id,
                                    'amount' => $totalAmount,
                                    'type' => 'bill', // Since it's a purchase
                                    'date' => $data['date'],
                                    'current_balance' => $record->balance + $totalAmount,
                                    'vendor_id' => $record->id,
                                ]);

                                // Update vendor balance
                                $record->increment('balance', $totalAmount);
                            });
                        })
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Bill created successfully')
                                ->body('The bill has been created and vendor balance has been updated.')
                        ),
                    Tables\Actions\ViewAction::make()
                        ->url(fn($record) => VendorResource::getUrl('view', ['record' => $record])),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'view' => Pages\ViewVendor::route('/{record}'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}

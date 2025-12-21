<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderDetailRelationManager;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('date')
                    ->default(now())
                    ->disabled()
                    ->hiddenLabel()
                    ->prefix('Date:')
                    ->dehydrated()
                    ->columnSpanFull(),
                Group::make()
                    ->Schema([

                        Section::make()
                            ->Description('Customer Information')
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $customer = Customer::find($state);
                                        $set('phone', $customer->phone ?? null);
                                        $set('address', $customer->address ?? null);
                                    }),
                                Placeholder::make('phone')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))->phone ?? '-'),
                                Placeholder::make('address')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))->address ?? '-'),
                            ])->columns(3),

                        Section::make()
                            ->description('Order Details')
                            ->schema([
                                Repeater::make('orderdetail')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->relationship('product', 'name')
                                            ->required()
                                            ->reactive()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $product = Product::find($state);
                                                $price = $product->price ?? 0;
                                                $set('price', $price);
                                                $qty = $get('qty') ?? 1;
                                                $subtotal = $price * $qty;
                                                $set('subtotal', $subtotal);

                                                $items = $get('../../orderdetail') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount');
                                                $discount_amount = $total * $discount / 100;
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            }),
                                        TextInput::make('price')
                                            ->readOnly()
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->formatStateUsing(fn($state, Get $get)
                                            => $state ?? Product::find($get('product_id'))->price ?? 0),
                                        TextInput::make('qty')
                                            ->default(1)
                                            ->numeric()
                                            ->reactive()
                                            ->minValue(1)
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = $get('price') ?? 0;
                                                $set('subtotal', $price * $state);

                                                $items = $get('../../orderdetail') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount');
                                                $discount_amount = $total * $discount / 100;
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            }),
                                        TextInput::make('subtotal')
                                            ->numeric()
                                            ->readOnly()
                                            ->default(0)
                                            ->prefix('IDR'),

                                    ])->columns(4)
                                    ->hiddenLabel()
                                    ->addAction(
                                        fn(Forms\Components\Actions\Action $action) => $action
                                            ->label('Add Product')
                                            ->color('primary')
                                            ->icon('heroicon-o-plus')
                                    ),
                            ]),

                    ])->columnSpan(2),

                Section::make()
                    ->description('Payment Information')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'canceled' => 'Canceled',
                                'completed' => 'Completed',
                            ])->default('new')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->columnSpanFull()
                            ->prefix('IDR'),
                        TextInput::make('discount')
                            ->columnSpan(2)
                            ->reactive()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->suffix('%')
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $discount = floatval($state) ?? 0;
                                $total_price = $get('total_price') ?? 0;
                                $discount_amount = $total_price * $discount / 100;
                                $set('discount_amount', $discount_amount);
                                $set('total_payment', $total_price - $discount_amount);
                            }),
                        TextInput::make('discount_amount')
                            ->columnSpan(2)
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('total_payment')
                            ->columnSpanFull()
                            ->disabled()
                            ->prefix('IDR')
                            ->dehydrated(),
                    ])->columnSpan(1)
                    ->columns(4),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('id')
                    ->label('Order ID'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                    TextColumn::make('total_price')
                    ->sortable()
                    ->prefix('IDR')
                    ->numeric(),

                    TextColumn::make('discount')
                    ->suffix('%'),

                    TextColumn::make('discount_amount')
                    ->prefix('IDR')
                    ->numeric(),

                    TextColumn::make('total_payment')
                    ->sortable()
                    ->prefix('IDR')
                    ->numeric(),

                    TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('date')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
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
            OrderDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

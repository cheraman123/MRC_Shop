<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Group; 
use Filament\Forms\Components\Repeater; 
use Filament\Forms\Components\Textarea; 
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('order Information')->schema([
                        Select::make('user_id')
                        ->label('Customer')
                        ->relationship('user','name')
                        ->searchable()
                        ->required(),

                        Select::make('payment_methord')
                        ->options([
                            'stripe'=>'Stripe',
                            'code'=> 'Cash on Delivery'
                        ])
                        ->required(),

                        Select::make('payment_status')
                        ->options([
                            'pending'=>'Pending',
                            'paid'=>'Paid',
                            'failed'=>'Failed'
                        ])
                        ->default('pending')
                        ->required(),

                        ToggleButtons::make('status')
                        ->inline()
                        ->default('New')
                        ->options([
                            'new'=>'New',
                            'processin'=>'Processing',
                            'shipping'=>'Shipping',
                            'delivered'=>'Delivered',
                            'cancelled'=>'Cancelled'
                        ])
                        ->colors([
                            'new'=>'info',
                            'processin'=>'warning',
                            'shipping'=>'success',
                            'delivered'=>'success',
                            'cancelled'=>'danger'
                        ])
                        ->icons([
                            'new'=>'heroicon-m-sparkles',
                            'processin'=>'heroicon-m-arrow-path',
                            'shipping'=>'heroicon-m-truck',
                            'delivered'=>'heroicon-m-check-badge',
                            'cancelled'=>'heroicon-m-x-circle'
                        ]),

                        Select::make('currencey')
                        ->options([
                            'inr'=>'INR',
                            'usd'=>'USD',
                            'eur'=>'EUR',
                            'gbp'=>'GBP'
                        ])
                        ->default('INR')
                        ->required(),

                        Select::make('shipping_methord')
                        ->options([
                            'fedex'=>'FedEx',
                            'ups'=>'UPS',
                            'dhl'=>'DHL',
                            'usps'=>'USPS'
                        ]),

                        Textarea::make('notes')
                        ->columnSpanFull()
                       
                    ])->columns(2),

                    Section::make('Order Items')->schema([
                        Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Select::make('product_id')
                            ->relationship('product','name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(4)
                            ->reactive()
                            ->afterStateUpdated(fn ($state,Set $set)=>$set('unit_amount',Product::find($state)?->price ?? 0))
                            ->afterStateUpdated(fn ($state,Set $set)=>$set('total_amount',Product::find($state)?->price ?? 0)),

                            TextInput::make('quentity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1)
                            ->columnSpan(2)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Set $set , Get $get)=> $set('total_amount',$state*$get('unit_amount'))),

                            TextInput::make('unit_amount')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(3),

                            TextInput::make('total_amount')
                            ->numeric()
                            ->required()
                            ->dehydrated() 
                            ->columnSpan(3),

                        ])->columns(12),

                        Placeholder::make('grand_total_placeholder')
                        ->label('Grand Total')
                        ->content(function (Get $get , Set $set) {
                            $total = 0;
                        if(!$repeaters = $get('items'))
                        {
                            return $total;
                        }

                        foreach($repeaters as $key => $repeater){
                            $total += $get ("items.{$key}.total_amount");
                        }
                               $set('grant_total',$total);
                               return Number::currency($total, 'INR'); 
                        }),

                        Hidden::make('grant_total')
                        ->default(0)

                    ])
                ])->columnSpanFull()


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                ->label('Customer')
                ->sortable()
                ->searchable(),

                TextColumn::make('grand_total')
                ->numeric()
                ->sortable()
                ->money('INR'),

                TextColumn::make('payment_method')
                ->searchable()
                ->sortable(),

                TextColumn::make('payment_status')
                ->searchable()
                ->sortable(),

                TextColumn::make('currency')
                ->sortable()
                ->searchable(),

                TextColumn::make('shipping_methord')
                ->sortable()
                ->searchable(),

                SelectColumn::make('status')
                ->optins([
                    'new'=>'New',
                    'processin'=>'Processing',
                    'shipping'=>'Shipping',
                    'delivered'=>'Delivered',
                    'cancelled'=>'Cancelled'
                ])
                ->searchable()
                ->sortable(),

                TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggleHiddenByDefault:true),

                TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggleHiddenByDefault:true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                ])
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
            AddressRelationManager::class
        ];
    }
    public static function getNavigationBade():?string{
        return static::getModel()::count();
    }

    public static  function getNavigationBadgeColor(): string|array|null{
        return static::getModel()::count() > 10 ? 'danger' : 'success';
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
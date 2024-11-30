<?php

namespace App\Filament\Widgets;


use App\Filament\Resources\OrderResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{

    
    
    protected int | string | array $columnSpan ='full';

    protected static ? int $scort = 2;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery())
                ->defaultPageinationPageOption(5)
                ->defaultSort('created_at','desc')
                
            ->columns([
                TextColumn::make('id')
                ->label('Order ID')
                ->searchable(),

                TextColumn::make('user.name')
                ->searchable(),

                TextColumn::make('grand_total')
                ->money('INR'),

                TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state){
                    'new'=>'info',
                    'processing' => 'warning',
                    'shipped' => 'success',
                    'delivered' => 'success',
                    'cancelled' => 'danger'
                })
                ->icon(fn(string $state): string => match ($state){
                    'new'=>'heroicon-m-sparkles',
                    'processing' => 'heroicon-m-arrow-path',
                    'shipped' => 'heroicon-m-truck',
                    'delivered' => 'heroicon-m-check-badge',
                    'cancelled' => 'heroicon-m-x-circle'
                })
                ->sortable(),

                TextColumn::make('payment_methord')
                ->sortable()
                ->searchable(),

                TextColumn::make('payment_status')
                ->sortable()
                ->badge()
                ->searchable(),

                TextColumn::make('cerated_at')
               ->label('Order Date')
               ->dateTime(), 
            ])
            ->actions([
                Action::make('View Order')
                ->url(fn (Order $record):string =>OrderResource::getUrl('view',['record' =>$recode]))
                ->icon('heroicon-m-eye'),
            ]);
    }
}

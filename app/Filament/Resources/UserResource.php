<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                ->required(),

                Forms\Components\TextInput::make('email')
                ->label('Email Address')
                ->email()
                ->maxlength(255)
                ->unique(ignoreRecord:true)
                ->required(),

                Forms\Components\DateTimePicker::make('email_verified_at')
                ->label('Email Verified At')
                ->default(now()),

                Forms\Components\TextInput::make('password')
                ->password()
                ->dehydrated(fn ($state)=>filled($state))
                ->required(fn ($livewire): bool => $livewire instanceof CreateRecord)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->Searchable(),
                Tables\Columns\TextColumn::make('email')
                ->Searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                ->dateTime()
                ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    ViewAction::make(),
                    DeleteAction::make(),
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
            OrdersRelationManager::class
        ];
    }

    public static function getGloballySearchableAttributes(): array
{
    return ['name','email'];
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

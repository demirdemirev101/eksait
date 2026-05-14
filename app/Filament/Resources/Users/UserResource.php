<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Администрация';
    protected static ?string $navigationLabel = 'Потребители';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Име')
                    ->required(),
                TextInput::make('email')
                    ->label('Email адрес')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефонен номер')
                    ->tel(),
                Select::make('roles')
                    ->label('Роля')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Име')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email адрес'),
                TextColumn::make('roles')
                    ->label('Роля')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->roles->first()?->name)
                    ->color(fn ($state) => match ($state) {
                        'customer' => 'info',
                        'admin' => 'primary',
                        default => 'gray',
                    }),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->authorize(fn (Model $record) => $record->id !== Auth::id()),
                DeleteAction::make()
                    ->authorize(fn (Model $record) => $record->id !== Auth::id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorize(fn ($records) => $records->contains(fn ($record) => $record->id !== Auth::id())),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}

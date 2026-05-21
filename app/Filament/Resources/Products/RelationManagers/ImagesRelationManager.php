<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Изображения';

    protected function afterSave(): void
    {
        $this->ownerRecord->refresh();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_primary')
                    ->label('Основно изображение')
                    ->default(fn (): bool => ! $this->getOwnerRecord()->images()->where('is_primary', true)->exists())
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Ред на изображението')
                    ->required()
                    ->numeric()
                    ->default(fn (): int => ((int) $this->getOwnerRecord()->images()->max('sort_order')) + 1),
                FileUpload::make('image_path')
                    ->label('Изображение')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('product-images')
                    ->maxSize(5120)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                IconColumn::make('is_primary')
                    ->label('Основно изображение')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ред на изображението')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image_path')
                    ->label('Изображение')
                    ->imageHeight(40)
                    ->circular()
                    ->disk('public'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Качи изображение'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактирай')
                    ->authorize(fn () => ImagesRelationManager::canEdit($this->getOwnerRecord())),
                DeleteAction::make()
                    ->label('Изтрий'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Изтрий избраните'),
                ]),
            ]);
    }
}

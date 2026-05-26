<?php

namespace App\Filament\Resources\HomeBanners;

use App\Filament\Resources\HomeBanners\Pages\ManageHomeBanners;
use App\Models\HomeBanner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HomeBannerResource extends Resource
{
    protected static ?string $model = HomeBanner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Съдържание';

    protected static ?string $navigationLabel = 'Банери';

    protected static ?string $modelLabel = 'банер';

    protected static ?string $pluralModelLabel = 'Банери';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    Toggle::make('is_active')
                        ->label('Активен')
                        ->default(true),
                    TextInput::make('sort_order')
                        ->label('Ред на показване')
                        ->numeric()
                        ->default(fn (): int => ((int) HomeBanner::query()->max('sort_order')) + 1)
                        ->minValue(1)
                        ->required(),
                    TextInput::make('eyebrow')
                        ->label('Надзаглавие')
                        ->maxLength(255),
                    TextInput::make('title')
                        ->label('Заглавие')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('subtitle')
                        ->label('Подзаглавие')
                        ->rows(3)
                        ->columnSpanFull(),
                    TextInput::make('button_text')
                        ->label('Текст на бутона')
                        ->maxLength(255),
                    TextInput::make('button_url')
                        ->label('Линк на бутона')
                        ->maxLength(255),
                    FileUpload::make('image')
                        ->label('Изображение')
                        ->image()
                        ->disk('public')
                        ->directory('banners')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Ред')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Заглавие')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('button_text')
                    ->label('Бутон')
                    ->placeholder('-'),
                ImageColumn::make('image')
                    ->label('Снимка')
                    ->disk('public')
                    ->height(44),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make()->label('Редактирай'),
                DeleteAction::make()->label('Изтрий'),
            ])
            ->headerActions([
                CreateAction::make()->label('Добави банер'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Изтрий избраните'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHomeBanners::route('/'),
        ];
    }
}

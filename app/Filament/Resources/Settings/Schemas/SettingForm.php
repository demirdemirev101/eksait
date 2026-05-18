<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Доставка')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('delivery_enabled')
                                ->label('Активна доставка')
                                ->default(true)
                                ->reactive(),
                            TextInput::make('free_delivery_over')
                                ->label('Безплатна доставка над')
                                ->numeric()
                                ->prefix('EUR ')
                                ->nullable()
                                ->helperText('Остави празно, ако няма безплатна доставка.')
                                ->disabled(fn ($get) => $get('delivery_enabled') === false),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Банер на начална страница')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('home_banner_eyebrow')
                                ->label('Надзаглавие')
                                ->maxLength(255),
                            TextInput::make('home_banner_title')
                                ->label('Заглавие')
                                ->maxLength(255),
                            Textarea::make('home_banner_subtitle')
                                ->label('Подзаглавие')
                                ->rows(3)
                                ->columnSpanFull(),
                            TextInput::make('home_banner_button_text')
                                ->label('Текст на бутона')
                                ->maxLength(255),
                            TextInput::make('home_banner_button_url')
                                ->label('Линк на бутона')
                                ->maxLength(255),
                            FileUpload::make('home_banner_image')
                                ->label('Изображение за банера')
                                ->image()
                                ->disk('public')
                                ->directory('banners')
                                ->visibility('public')
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}


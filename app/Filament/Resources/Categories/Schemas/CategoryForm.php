<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                TextInput::make('slug')
                                    ->disabled(),
                            ]),

                        TextInput::make('sort_order')
                            ->label('Tartib raqami')
                            ->helperText('Saytda kategoriyalar shu raqam bo\'yicha o\'sish tartibida chiqadi.')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(1000000)
                            ->required()
                            ->default(fn () => Category::nextSortOrder()),

                        FileUpload::make('image')
                            ->image()
                            ->directory('categories'),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ])
            ]);
    }
}

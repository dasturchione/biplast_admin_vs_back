<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategoriya malumotlari')->schema([
                    TextInput::make('name')
                        ->label('Kategoriya nomi')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Faollik holati')
                        ->required()
                        ->default(true)
                ])->columnSpanFull()
            ]);
    }
}

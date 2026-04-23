<?php

namespace App\Filament\Resources\Blogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make("Blog malumotlari")->schema([
                        TextInput::make('title')
                            ->required(),
                        TextInput::make('slug')
                            ->disabled(),
                        TextInput::make('excerpt')
                            ->label('Qisqacha malumot')
                            ->required(),
                        MarkdownEditor::make('content')
                            ->label('Asosiy malumot')
                            ->required()
                            ->columnSpanFull(),

                    ])->columns(2),
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make("Blog kategoriya")->schema([
                        Select::make('blog_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                    Section::make("Rasm")->schema([
                        FileUpload::make('featured_image')
                            ->image()
                            ->required()
                            ->directory('blogs')
                            ->imageEditor(),
                    ]),

                    Section::make("Publikatsiya")->schema([
                        Toggle::make('is_published')
                            ->default(true)
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->default(now()),
                    ]),
                ])->columnSpan(1)
            ])->columns(3);
    }
}

<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->schema([
                    Section::make("Mahsulot malumotlari")->schema([
                        TextInput::make('name')
                            ->required(),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),
                    ])->columns(2),
                    Section::make("Rasmlar")->schema([
                        Repeater::make('images')
                            ->relationship() // 🔥 bu repeaterda ishlaydi
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('products')
                                    ->imageEditor()
                                    ->required(),
                            ])
                            ->reorderable()
                            ->columnSpanFull()
                    ])->columnSpan(2)
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make("Narxi")
                        ->schema([
                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('UZS'),
                        ]),
                    Section::make("Kategoriya")
                        ->schema([
                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Section::make("Status")
                        ->schema([
                            Toggle::make('is_active')
                                ->required()
                                ->default(true)
                        ]),
                    Section::make('SEO')
                        ->relationship('seo') // 🔥 MUHIM
                        ->schema([
                            TextInput::make('meta_title')
                                ->maxLength(60),

                            Textarea::make('meta_description')
                                ->rows(3)
                                ->maxLength(160),

                            TextInput::make('meta_keywords'),

                            TextInput::make('og_title'),

                            Textarea::make('og_description'),

                            FileUpload::make('og_image')
                                ->image()
                                ->directory('seo')
                                ->disk('public'),
                        ])
                        ->collapsible()
                ])->columnSpan(1)
            ])->columns(3);
    }
}

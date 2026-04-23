<?php

namespace App\Filament\Resources\Banners\Schemas;

use App\Models\Banner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Select::make('type')
                    ->options([
                        'image' => 'Rasm',
                        'video' => 'Video',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('file', null); // 🔥 file ni reset qiladi
                    }),
                FileUpload::make('file')
                    ->required()
                    ->directory('banners')
                    ->reactive()
                    ->reorderable()
                    ->key(fn($get) => 'file-' . $get('type'))
                    ->acceptedFileTypes(
                        function ($get) {
                            $type = $get('type');

                            if ($type === 'video') {
                                return ['video/mp4', 'video/webm'];
                            }

                            if ($type === 'image') {
                                return ['image/jpeg', 'image/png', 'image/webp'];
                            }

                            return []; // hech narsa tanlanmaguncha yuklamaydi
                        }
                    )
                    ->disabled(fn($get) => !$get('type')),
                TextInput::make('link'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

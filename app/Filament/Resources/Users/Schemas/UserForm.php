<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("User malumotlari")->schema([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->required(),
                    DateTimePicker::make('email_verified_at')
                        ->default(now()),
                    TextInput::make('password')
                        ->password()
                        ->required(),
                ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name_uz')
                ->label('Nomi (UZ)')
                ->state(fn (Product $record): string => $record->getTranslation('name', 'uz', false) ?? ''),
            ExportColumn::make('name_ru')
                ->label('Nomi (RU)')
                ->state(fn (Product $record): string => $record->getTranslation('name', 'ru', false) ?? ''),
            ExportColumn::make('description_uz')
                ->label('Tavsif (UZ)')
                ->state(fn (Product $record): string => $record->getTranslation('description', 'uz', false) ?? ''),
            ExportColumn::make('description_ru')
                ->label('Tavsif (RU)')
                ->state(fn (Product $record): string => $record->getTranslation('description', 'ru', false) ?? ''),
            ExportColumn::make('artikul')
                ->label('Artikul'),
            ExportColumn::make('weight')
                ->label('Vazni (gram)'),
            ExportColumn::make('size')
                ->label("O'lchami"),
            ExportColumn::make('packaging')
                ->label('Qadoqdagi soni'),
            ExportColumn::make('price')
                ->label('Narxi'),
            ExportColumn::make('category')
                ->label('Kategoriya')
                ->state(fn (Product $record): string => $record->category?->getTranslation('name', 'uz', false) ?? ''),
            ExportColumn::make('is_active')
                ->label('Faol')
                ->state(fn (Product $record): string => $record->is_active ? '1' : '0'),
            ExportColumn::make('colors')
                ->label('Ranglar')
                ->state(function (Product $record): string {
                    return $record->colors
                        ->map(fn ($color) => trim(($color->name ? $color->name . ':' : '') . $color->code))
                        ->implode(';');
                }),
            ExportColumn::make('slug')
                ->label('Slug'),
            ExportColumn::make('created_at')
                ->label('Yaratilgan sana'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Mahsulot eksporti yakunlandi: ' . Number::format($export->successful_rows) . ' ta qator eksport qilindi.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ta qatorda xatolik yuz berdi.';
        }

        return $body;
    }
}

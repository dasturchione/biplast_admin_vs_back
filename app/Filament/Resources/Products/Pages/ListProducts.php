<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Actions\ExcelImportAction;
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;

class ListProducts extends ListRecords
{
    use Translatable;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ExcelImportAction::make()
                ->label('Excel import')
                ->importer(ProductImporter::class),
            ExportAction::make()
                ->label('Excel export')
                ->exporter(ProductExporter::class),
            CreateAction::make(),
        ];
    }
}

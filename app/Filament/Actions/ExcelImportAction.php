<?php

namespace App\Filament\Actions;

use Filament\Actions\ImportAction;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Filament-ning native ImportAction'i faqat CSV o'qiydi.
 * Bu kengaytma .xlsx va .xls fayllarni ham qabul qiladi: yuklangan
 * spreadsheet'ni vaqtinchalik CSV oqimiga aylantirib, qolgan barcha
 * import jarayonini (validatsiya, ustun moslash, batch, xato hisoboti)
 * ona klassga topshiradi.
 */
class ExcelImportAction extends ImportAction
{
    /** @var list<string> */
    protected array $spreadsheetExtensions = ['xlsx', 'xls', 'ods'];

    protected function setUp(): void
    {
        parent::setUp();

        // FileUpload qabul qiladigan fayl turlariga Excel mime'larini qo'shamiz.
        $parentSchema = $this->schema;

        $this->schema(function () use ($parentSchema): array {
            $components = $this->evaluate($parentSchema);

            foreach ($components as $component) {
                if ($component instanceof FileUpload && $component->getName() === 'file') {
                    $component->acceptedFileTypes([
                        // CSV
                        'text/csv', 'text/x-csv', 'application/csv', 'application/x-csv',
                        'text/comma-separated-values', 'text/x-comma-separated-values', 'text/plain',
                        // XLS / XLSX / ODS
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.oasis.opendocument.spreadsheet',
                        'application/octet-stream',
                        'application/zip',
                    ]);
                }
            }

            return $components;
        });
    }

    /**
     * @return array<mixed>
     */
    public function getFileValidationRules(): array
    {
        $rules = parent::getFileValidationRules();

        foreach ($rules as $index => $rule) {
            if (is_string($rule) && str_starts_with($rule, 'extensions:')) {
                $rules[$index] = 'extensions:csv,txt,' . implode(',', $this->spreadsheetExtensions);
            }
        }

        return $rules;
    }

    /**
     * @return resource | false
     */
    public function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $this->spreadsheetExtensions, true)) {
            // CSV/TXT — ona klass mantiqi (kodirovka aniqlash bilan)
            return parent::getUploadedFileStream($file);
        }

        return $this->convertSpreadsheetToCsvStream($file);
    }

    /**
     * Excel/ODS faylni UTF-8 CSV oqimiga aylantiradi.
     *
     * @return resource
     */
    protected function convertSpreadsheetToCsvStream(TemporaryUploadedFile $file)
    {
        $path = $file->getRealPath();

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $stream = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

        foreach ($worksheet->toArray(null, true, false, false) as $row) {
            // To'liq bo'sh qatorlarni o'tkazib yuboramiz
            $hasValue = false;
            foreach ($row as $cell) {
                if (filled($cell)) {
                    $hasValue = true;
                    break;
                }
            }

            if (! $hasValue) {
                continue;
            }

            fputcsv(
                $stream,
                array_map(static fn ($value): string => $value === null ? '' : (string) $value, $row),
            );
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        rewind($stream);

        return $stream;
    }
}

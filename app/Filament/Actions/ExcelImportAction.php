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

        $rows = $worksheet->toArray(null, true, false, false);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        // Qaysi ustun indekslarida hech bo'lmasa bitta qiymat borligini aniqlaymiz.
        // Butunlay bo'sh ustunlar (ghost columns / nomsiz sarlavhalar) CSV'ga tushmasligi
        // kerak — aks holda header qatorida bir nechta bo'sh katak paydo bo'lib,
        // Filament "birdan ortiq bo'sh ustun sarlavhasi" xatosini beradi.
        $nonEmptyColumns = [];

        foreach ($rows as $row) {
            foreach ($row as $columnIndex => $cell) {
                if (filled($cell)) {
                    $nonEmptyColumns[$columnIndex] = true;
                }
            }
        }

        ksort($nonEmptyColumns);
        $nonEmptyColumns = array_keys($nonEmptyColumns);

        // Barcha ustunlar bo'sh bo'lsa — yozadigan narsa yo'q.
        if ($nonEmptyColumns === []) {
            rewind($stream);

            return $stream;
        }

        foreach ($rows as $row) {
            // To'liq bo'sh qatorlarni o'tkazib yuboramiz
            $hasValue = false;
            foreach ($nonEmptyColumns as $columnIndex) {
                if (filled($row[$columnIndex] ?? null)) {
                    $hasValue = true;
                    break;
                }
            }

            if (! $hasValue) {
                continue;
            }

            // Faqat ma'lumotli ustunlarni saqlaymiz.
            $filteredRow = [];
            foreach ($nonEmptyColumns as $columnIndex) {
                $value = $row[$columnIndex] ?? null;
                $filteredRow[] = $value === null ? '' : (string) $value;
            }

            fputcsv($stream, $filteredRow);
        }

        rewind($stream);

        return $stream;
    }
}

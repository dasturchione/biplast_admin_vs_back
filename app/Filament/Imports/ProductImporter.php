<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            // ID — export faylni qayta import qilganda mavjud mahsulotni topish uchun (ixtiyoriy).
            // O'zi DB ustuni bo'lsa ham, uni qayta yozmaymiz (resolveRecord ishlatadi).
            ImportColumn::make('id')
                ->label('ID')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'integer']),
            // Quyidagi virtual ustunlar haqiqiy DB ustuni emas — ular fillRecord()/afterSave()
            // ichida qo'lda yoziladi, shuning uchun avtomatik to'ldirishni o'chiramiz.
            ImportColumn::make('name_uz')
                ->label('Nomi (UZ)')
                ->requiredMapping()
                ->fillRecordUsing(fn () => null)
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('name_ru')
                ->label('Nomi (RU)')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('description_uz')
                ->label('Tavsif (UZ)')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string']),
            ImportColumn::make('description_ru')
                ->label('Tavsif (RU)')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string']),
            ImportColumn::make('artikul')
                ->label('Artikul')
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('weight')
                ->label('Vazni (gram)')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('size')
                ->label("O'lchami")
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('packaging')
                ->label('Qadoqdagi soni')
                ->numeric()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('price')
                ->label('Narxi')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('category')
                ->label('Kategoriya (nomi yoki slug)')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('is_active')
                ->label('Faol')
                ->boolean()
                ->rules(['nullable', 'boolean']),
            // Ranglar format: "Qizil:#FF0000;Ko'k:#0000FF" yoki "#FF0000;#0000FF"
            ImportColumn::make('colors')
                ->label('Ranglar')
                ->fillRecordUsing(fn () => null)
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): Product
    {
        // 1) ID berilgan bo'lsa (export faylni qayta import qilish) — shu mahsulotni yangilaymiz
        if (filled($this->data['id'] ?? null)) {
            $existing = Product::find($this->data['id']);

            if ($existing) {
                return $existing;
            }
        }

        // 2) Artikul to'ldirilgan bo'lsa — artikul bo'yicha topamiz yoki yangi yaratamiz (upsert)
        if (filled($this->data['artikul'] ?? null)) {
            return Product::firstOrNew([
                'artikul' => $this->data['artikul'],
            ]);
        }

        // 3) Aks holda har doim yangi mahsulot yaratamiz
        return new Product();
    }

    public function fillRecord(): void
    {
        parent::fillRecord();

        // Tarjimali maydonlar (uz / ru)
        $this->record->setTranslation('name', 'uz', (string) ($this->data['name_uz'] ?? ''));

        if (filled($this->data['name_ru'] ?? null)) {
            $this->record->setTranslation('name', 'ru', (string) $this->data['name_ru']);
        }

        if (filled($this->data['description_uz'] ?? null)) {
            $this->record->setTranslation('description', 'uz', (string) $this->data['description_uz']);
        }

        if (filled($this->data['description_ru'] ?? null)) {
            $this->record->setTranslation('description', 'ru', (string) $this->data['description_ru']);
        }

        // Slug ru nomidan generatsiya qilinadi; ru bo'lmasa uz dan to'ldiramiz
        if (blank($this->record->getTranslation('name', 'ru', false))) {
            $this->record->setTranslation('name', 'ru', $this->record->getTranslation('name', 'uz'));
        }

        // Kategoriya: nom yoki slug bo'yicha topish, bo'lmasa yaratish
        if (filled($this->data['category'] ?? null)) {
            $this->record->category_id = $this->resolveCategoryId((string) $this->data['category']);
        }

        if (! isset($this->data['is_active'])) {
            $this->record->is_active ??= true;
        }
    }

    protected function resolveCategoryId(string $value): int
    {
        $value = trim($value);

        $category = Category::query()
            ->where('slug', $value)
            ->orWhereRaw("JSON_EXTRACT(name, '$.uz') = ?", [$value])
            ->orWhereRaw("JSON_EXTRACT(name, '$.ru') = ?", [$value])
            ->first();

        if (! $category) {
            $category = new Category();
            $category->setTranslation('name', 'uz', $value);
            $category->setTranslation('name', 'ru', $value);
            $category->is_active = true;
            $category->save();
        }

        return $category->id;
    }

    protected function afterSave(): void
    {
        $raw = $this->data['colors'] ?? null;

        if (blank($raw)) {
            return;
        }

        // Qayta importda eski ranglarni almashtiramiz
        $this->record->colors()->delete();

        foreach (explode(';', (string) $raw) as $part) {
            $part = trim($part);
            if ($part === '') {
                continue;
            }

            if (str_contains($part, ':')) {
                [$name, $code] = array_map('trim', explode(':', $part, 2));
            } else {
                $name = null;
                $code = $part;
            }

            if ($code === '') {
                continue;
            }

            $this->record->colors()->create([
                'name' => $name ?: null,
                'code' => $code,
            ]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Mahsulot importi yakunlandi: ' . Number::format($import->successful_rows) . ' ta qator import qilindi.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ta qatorda xatolik yuz berdi.';
        }

        return $body;
    }
}

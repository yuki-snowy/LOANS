<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'property_id',
    'path',
    'sort_order',
])]
class PropertyImage extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * 公開URL（ホスト依存を避けるためルート相対）。
     * 実ファイルが無い場合は空文字（リンク切れ表示を防ぐ）。
     */
    public function url(): string
    {
        $path = $this->normalizedPath();
        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return '';
        }

        return '/storage/'.$path;
    }

    public function isAvailable(): bool
    {
        return $this->url() !== '';
    }

    private function normalizedPath(): string
    {
        if (! is_string($this->path) || $this->path === '') {
            return '';
        }

        return ltrim(str_replace('\\', '/', $this->path), '/');
    }
}

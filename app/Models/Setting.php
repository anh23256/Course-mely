<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public $timestamps = true;

    public static function allCached()
    {
        return Cache::rememberForever('settings', function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    // Lấy giá trị của 1 setting theo key
    public static function get($key, $default = null)
    {
        $settings = self::allCached();
        return $settings[$key] ?? $default;
    }

    // Cập nhật giá trị setting và xóa cache
    public static function set($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings'); // Xóa cache cũ để load lại dữ liệu mới
    }
}

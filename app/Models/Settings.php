<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'value',
    ];
    public static function get($key)
    {
        $setting = Settings::where('key', $key)->first();
        if($setting) {
            return $setting->value;
        }
        return null;
    }
}

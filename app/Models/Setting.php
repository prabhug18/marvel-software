<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /**
     * Get setting value by key with optional default
     */
    public static function get($key, $default = null)
    {
        $rec = static::where('key', $key)->first();
        if (!$rec) return $default;
        // Attempt to JSON-decode if stored as JSON
        $val = $rec->value;
        $decoded = json_decode($val, true);
        return $decoded === null ? $val : $decoded;
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value)
    {
        $toStore = is_array($value) || is_object($value) ? json_encode($value) : $value;
        return static::updateOrCreate(['key' => $key], ['value' => $toStore]);
    }
}

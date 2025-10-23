<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_text',
        'media_path',
        'category_id',
        'category',
        'is_active'
    ];
    public function options()
    {
        return $this->hasMany(DiscOption::class, 'question_id');
    }
    
    // Override toArray untuk memastikan options dikirim dengan benar
    public function toArray()
    {
        $array = parent::toArray();
        $array['options'] = $this->options->map(function($option) {
            return [
                'id' => $option->id,
                'option_text' => $option->option_text,
                'dimension_most' => $option->dimension_most,
                'dimension_least' => $option->dimension_least,
            ];
        });
        return $array;
    }

    public function category()
    {
        return $this->belongsTo(DiscCategory::class);
    }
}

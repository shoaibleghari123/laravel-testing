<?php

namespace App\Models;

use App\Exceptions\CurrencyRateNotFoundException;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder;
class Product extends Model
{
    use HasFactory;

    protected $table = 'product';
    protected $fillable = ['name', 'price', 'is_published', 'published_at', 'photo','youtube_id', 'youtube_thumbnail'];

    public function getPriceEurAttribute()
    {
        try {
            return (new CurrencyService())->convert($this->price, 'USD', 'EUR');
        }catch (CurrencyRateNotFoundException $e) {
            //alert someone- do logic here
            return 0;
        }

    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value * 100;
    }

    public function scopePublished(Builder $query) : Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    /**
     * PERBAIKAN: Definisikan kolom mana saja yang boleh diisi
     * menggunakan method create() atau update(). Ini adalah
     * fitur keamanan Mass Assignment di Laravel/Lumen.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'variant',
        'ordinal',
    ];

    /**
     * Relasi Many-to-Many ke Opsi Variasi.
     * Satu Varian (misal: "Baju Merah, L") terhubung ke BANYAK Opsi
     * ("Merah" dan "L") melalui sebuah tabel pivot.
     */
    public function options()
    {
        return $this->belongsToMany(
            ProductVariationOption::class,
            'product_variant_options',
            'product_variant_id',
            'product_variation_option_id'
        );
    }
    public function values()
    {
        return $this->hasMany(ProductVariantValue::class, 'variant_id');
    }
}

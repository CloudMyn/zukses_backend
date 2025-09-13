<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'parent_id',
        'size_guide',
        'shipping_information',
        'dimensions',
        'price_admin'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function allChildren()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->with('allChildren');
    }
    // public function childrenNew()
    // {
    //     return $this->hasMany(Category::class, 'parent_id');
    // }

    public function getChildrenByLevel($level, $currentLevel = 1)
    {
        $count = 0;
        foreach ($this->children as $child) {
            if ($currentLevel === $level) {
                $count++;
            } else {
                $count += $child->getChildrenByLevel($level, $currentLevel + 1);
            }
        }
        return $count;
    }

    /**
     * Hitung total produk termasuk turunan
     */
    public function getTotalProductsCountAttribute()
    {
        $count = $this->products()->count();

        foreach ($this->allChildren as $child) {
            $count += $child->total_products_count; // rekursif
        }

        return $count;
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->with('children');
    }
    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    // Recursive function to get full category name
    public function getFullCategoryName()
    {
        if ($this->parent) {
            return $this->parent->getFullCategoryName() . ' > ' . $this->name;
        }
        return $this->name;
    }

    // 🔹 Tambahan baru untuk ambil semua child recursive
    public function childrenRecursive()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id', 'id')->with('childrenRecursive');
    }

    public function scopeChildrenAtLevel($query, $parentId, $level)
    {
        if ($level == 1) {
            return $query->where('parent_id', $parentId);
        }

        // Rekursif, join ke parent
        return $query->whereHas('parent', function ($q) use ($parentId, $level) {
            $q->childrenAtLevel($parentId, $level - 1);
        });
    }

    public function childrenNew()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->with('children');
    }
}

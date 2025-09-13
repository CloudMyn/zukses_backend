<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Pakaian Wanita',
                'data' => [
                    [
                        'name' => 'Atasan',
                        'data' => [
                            [
                                'name' => 'Tanktop & Kamisol',
                            ],
                            [
                                'name' => 'Kemben',
                            ],
                            [
                                'name' => 'Kaos',
                            ],
                            [
                                'name' => 'Kemeja & Blouse',
                            ],
                            [
                                'name' => 'Kaus Polo',
                            ],
                            [
                                'name' => 'Bodysuit',
                            ],
                            [
                                'name' => 'Atasan Lainnya',
                            ]
                        ]
                    ],
                    [
                        'name' => 'Celana Panjang & Legging',
                        'data' => [
                            [
                                'name' => 'Legging & Tregging',
                            ],
                            [
                                'name' => 'Celana Panjang',
                            ],
                            [
                                'name' => 'Celana Panjang & Legging Lainnya',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Celana Pendek',
                        'data' => [
                            [
                                'name' => 'Celana Pendek',
                            ],
                            [
                                'name' => 'Rok Celana',
                            ],
                            [
                                'name' => 'Celana Pendek Lainnya',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Rok',
                    ],
                    [
                        'name' => 'Celana Jeans',
                    ],
                    [
                        'name' => 'Dress',
                    ],
                ],
            ],
            [
                'name' => 'Pakaian Pria',
                'data' => [
                    [
                        'name' => 'Celana Panjang Jeans',
                    ],
                    [
                        'name' => 'Hoodie & Sweatshirt',
                        'data' => [
                            [
                                'name' => 'Hoodie',
                            ],
                            [
                                'name' => 'Sweatshirt',
                            ],
                            [
                                'name' => 'Hoodie & Sweatshirt Lainnya',
                            ],
                        ]
                    ],
                    [
                        'name' => 'Sweater & Cardigan',
                    ],
                    [
                        'name' => 'Jaket, Mantel, & Rompi',
                        'data' => [
                            [
                                'name' => 'Jaket & Mantel Musim Dingin',
                            ],
                            [
                                'name' => 'Jaket',
                            ],
                            [
                                'name' => 'Rompi',
                            ],
                            [
                                'name' => 'Jaket, Mantel, & Rompi Lainnya',
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $this->createCategoriesRecursive($categories, null);
    }

    private function createCategoriesRecursive($categories, $parentId = null): void
    {
        foreach ($categories as $categoryData) {
            // Buat kategori di database
            $category = ProductCategory::create([
                'name'      => $categoryData['name'],
                'parent_id' => $parentId,
            ]);

            // Jika ada sub-kategori (key 'data' ada dan berupa array),
            // panggil fungsi ini lagi untuk sub-kategori tersebut
            // dengan parent_id dari kategori yang baru saja dibuat.
            if (isset($categoryData['data']) && is_array($categoryData['data'])) {
                $this->createCategoriesRecursive($categoryData['data'], $category->id);
            }
        }
    }
}

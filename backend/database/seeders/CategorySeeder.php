<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kerti gépek',
                'description' => 'Kerti és udvari munkákhoz használható gépek.',
                'active' => true,
            ],
            [
                'name' => 'Takarítógépek',
                'description' => 'Otthoni, kültéri és autótakarításhoz használható gépek.',
                'active' => true,
            ],
            [
                'name' => 'Barkács- és építőipari kisgépek',
                'description' => 'Felújításhoz, barkácsoláshoz és kisebb építési munkákhoz.',
                'active' => true,
            ],
            [
                'name' => 'Létrák, állványok és segédeszközök',
                'description' => 'Kiegészítő és segédeszközök ház körüli munkákhoz.',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                [
                    'name' => $category['name'],
                ],
                $category
            );
        }
    }
}

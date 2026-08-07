<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\WorkType;
use Illuminate\Database\Seeder;
use RuntimeException;

class WorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $workTypes = [
            [
                'name' => 'Kert rendbetétele',
                'slug' => 'kert-rendbetetele',
                'description' => 'Kerti és udvari munkákhoz ajánlott gépek.',
                'icon_key' => 'garden',
                'active' => true,
                'sort_order' => 10,
                'products' => [
                    'FUNYIRO-BENZIN',
                    'SZEGELYNYIRO-AKKU',
                    'SZEGELYNYIRO-VEZETEKES',
                    'FUSZELLOZTETO-VEZETEKES',
                    'AGAPRITO-VEZETEKES',
                    'TALICSKA',
                ],
            ],

            [
                'name' => 'Fűnyírás és szegélynyírás',
                'slug' => 'funyiras-es-szegelynyiras',
                'description' => 'Fűnyíráshoz és a gyep széleinek rendezéséhez.',
                'icon_key' => 'garden',
                'active' => true,
                'sort_order' => 20,
                'products' => [
                    'FUNYIRO-BENZIN',
                    'SZEGELYNYIRO-AKKU',
                    'SZEGELYNYIRO-VEZETEKES',
                ],
            ],

            [
                'name' => 'Kerti nyesedék kezelése',
                'slug' => 'kerti-nyesedek-kezelese',
                'description' => 'Ágak és kerti nyesedék feldolgozásához.',
                'icon_key' => 'garden',
                'active' => true,
                'sort_order' => 30,
                'products' => [
                    'AGAPRITO-VEZETEKES',
                    'TALICSKA',
                ],
            ],

            [
                'name' => 'Takarítás',
                'slug' => 'takaritas',
                'description' => 'Otthoni, műhely- és kültéri takarítási feladatokhoz.',
                'icon_key' => 'cleaning',
                'active' => true,
                'sort_order' => 40,
                'products' => [
                    'KARPITTISZTITO',
                    'MAGASNYOMASU-MOSO',
                    'VIZES-PORSZIVO',
                    'GOZOS-ABLAKTISZTITO',
                ],
            ],

            [
                'name' => 'Autóápolás',
                'slug' => 'autoapolas',
                'description' => 'Autók külső és belső tisztításához, ápolásához.',
                'icon_key' => 'cleaning',
                'active' => true,
                'sort_order' => 50,
                'products' => [
                    'KARPITTISZTITO',
                    'MAGASNYOMASU-MOSO',
                    'POLIROZOGEP',
                    'VIZES-PORSZIVO',
                ],
            ],

            [
                'name' => 'Barkácsolás',
                'slug' => 'barkacsolas',
                'description' => 'Általános ház körüli szerelési és barkácsmunkákhoz.',
                'icon_key' => 'diy',
                'active' => true,
                'sort_order' => 60,
                'products' => [
                    'FURO-CSAVAROZO-AKKU',
                    'UTVEFURO-VEZETEKES',
                    'SAROKCSISZOLO-VEZETEKES',
                    'SAROKCSISZOLO-AKKU',
                    'GEPI-GYALU-VEZETEKES',
                    'EXCENTERES-CSISZOLO',
                    'KORFURESZ-VEZETEKES',
                ],
            ],

            [
                'name' => 'Fa megmunkálása',
                'slug' => 'fa-megmunkalasa',
                'description' => 'Faanyag vágásához, gyalulásához és csiszolásához.',
                'icon_key' => 'woodworking',
                'active' => true,
                'sort_order' => 70,
                'products' => [
                    'GEPI-GYALU-VEZETEKES',
                    'EXCENTERES-CSISZOLO',
                    'KORFURESZ-VEZETEKES',
                ],
            ],

            [
                'name' => 'Fúrás és szerelés',
                'slug' => 'furas-es-szereles',
                'description' => 'Fúrási, csavarozási és szerelési munkákhoz.',
                'icon_key' => 'diy',
                'active' => true,
                'sort_order' => 80,
                'products' => [
                    'FURO-CSAVAROZO-AKKU',
                    'UTVEFURO-VEZETEKES',
                ],
            ],

            [
                'name' => 'Csiszolás és vágás',
                'slug' => 'csiszolas-es-vagas',
                'description' => 'Fém- és faanyagok vágásához, csiszolásához.',
                'icon_key' => 'diy',
                'active' => true,
                'sort_order' => 90,
                'products' => [
                    'SAROKCSISZOLO-VEZETEKES',
                    'SAROKCSISZOLO-AKKU',
                    'EXCENTERES-CSISZOLO',
                    'KORFURESZ-VEZETEKES',
                ],
            ],

            [
                'name' => 'Magasban végzett munka',
                'slug' => 'magasban-vegzett-munka',
                'description' => 'Létrát vagy állványt igénylő ház körüli munkákhoz.',
                'icon_key' => 'diy',
                'active' => true,
                'sort_order' => 100,
                'products' => [
                    'LETRA-3X11',
                    'ALLVANY-3M',
                    'FEM-TARTOBAK-PAR',
                ],
            ],

            [
                'name' => 'Betonozás és anyagmozgatás',
                'slug' => 'betonozas-es-anyagmozgas',
                'description' => 'Kisebb építési és anyagmozgatási munkákhoz.',
                'icon_key' => 'concrete',
                'active' => true,
                'sort_order' => 110,
                'products' => [
                    'BETONKEVERO-180L',
                    'TALICSKA',
                    'FEM-TARTOBAK-PAR',
                ],
            ],
        ];

        foreach ($workTypes as $workTypeData) {
            $productSkus = $workTypeData['products'];

            unset($workTypeData['products']);

            $workType = WorkType::query()->updateOrCreate(
                [
                    'slug' => $workTypeData['slug'],
                ],
                $workTypeData
            );

            $productIds = Product::query()
                ->whereIn('sku', $productSkus)
                ->pluck('id');

            if ($productIds->count() !== count($productSkus)) {
                $foundSkus = Product::query()
                    ->whereIn('sku', $productSkus)
                    ->pluck('sku')
                    ->all();

                $missingSkus = array_diff(
                    $productSkus,
                    $foundSkus
                );

                throw new RuntimeException(
                    'Hiányzó termék SKU a WorkTypeSeederben: '
                    . implode(', ', $missingSkus)
                );
            }

            $workType->products()->sync(
                $productIds->all()
            );
        }
    }
}
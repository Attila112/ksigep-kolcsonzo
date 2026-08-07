<?php

return [
    [
        'category' => 'Kerti gépek',
        'name' => 'Benzines fűnyíró',
        'sku' => 'FUNYIRO-BENZIN',
        'inventory_prefix' => 'FUN',
        'description' => 'Benzines fűnyíró ház körüli és kerti használatra.',
        'price_per_day' => 6000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Kerti gépek',
        'name' => 'Akkumulátoros szegélynyíró',
        'sku' => 'SZEGELYNYIRO-AKKU',
        'inventory_prefix' => 'ASZ',
        'description' => 'Akkumulátoros szegélynyíró ház körüli és kerti használatra.',
        'price_per_day' => 8000,
        'deposit' => 20000,
        'inventory_quantity' => 1,

        'battery_system' => [
            'manufacturer' => 'Makita',
            'name' => 'LXT 18V',
        ],
        'required_batteries' => 2,
        'required_chargers' => 1,
    ],

    [
        'category' => 'Kerti gépek',
        'name' => 'Vezetékes szegélynyíró',
        'sku' => 'SZEGELYNYIRO-VEZETEKES',
        'inventory_prefix' => 'VSZ',
        'description' => 'Vezetékes szegélynyíró. Igény esetén hosszabbító külön bérelhető.',
        'price_per_day' => 4000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Kerti gépek',
        'name' => 'Vezetékes fűszellőztető',
        'sku' => 'FUSZELLOZTETO-VEZETEKES',
        'inventory_prefix' => 'FSZ',
        'description' => 'Vezetékes fűszellőztető. Igény esetén hosszabbító külön bérelhető.',
        'price_per_day' => 4000,
        'deposit' => 15000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Kerti gépek',
        'name' => 'Vezetékes ágaprító',
        'sku' => 'AGAPRITO-VEZETEKES',
        'inventory_prefix' => 'AGP',
        'description' => 'Elektromos ágaprító kerti nyesedék feldolgozásához. Igény esetén hosszabbító külön bérelhető.',
        'price_per_day' => 6000,
        'deposit' => 20000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Takarítógépek',
        'name' => 'Kárpittisztító',
        'sku' => 'KARPITTISZTITO',
        'inventory_prefix' => 'KAR',
        'description' => 'Kárpitok, szőnyegek és autóbelsők tisztításához.',
        'price_per_day' => 5000,
        'deposit' => 15000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Takarítógépek',
        'name' => 'Magasnyomású mosó',
        'sku' => 'MAGASNYOMASU-MOSO',
        'inventory_prefix' => 'MNM',
        'description' => 'Magasnyomású mosó a hozzá tartozó alap kiegészítőkkel.',
        'price_per_day' => 8000,
        'deposit' => 15000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Takarítógépek',
        'name' => 'Polírozógép',
        'sku' => 'POLIROZOGEP',
        'inventory_prefix' => 'POL',
        'description' => 'Polírozógép autóápoláshoz, a szükséges alap kiegészítőkkel.',
        'price_per_day' => 3000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Takarítógépek',
        'name' => 'Nagy teljesítményű vizes porszívó',
        'sku' => 'VIZES-PORSZIVO',
        'inventory_prefix' => 'NPV',
        'description' => 'Nagy teljesítményű nedves-száraz porszívó.',
        'price_per_day' => 4000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Takarítógépek',
        'name' => 'Gőzös ablaktisztító',
        'sku' => 'GOZOS-ABLAKTISZTITO',
        'inventory_prefix' => 'GAB',
        'description' => 'Gőzös ablaktisztító otthoni takarításhoz.',
        'price_per_day' => 4000,
        'deposit' => 15000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Akkumulátoros fúró-csavarozó',
        'sku' => 'FURO-CSAVAROZO-AKKU',
        'inventory_prefix' => 'AFU',
        'description' => 'Akkumulátoros fúró-csavarozó ház körüli szerelési és barkácsmunkákhoz.',
        'price_per_day' => 4000,
        'deposit' => 10000,
        'inventory_quantity' => 2,

        'battery_system' => [
            'manufacturer' => 'Makita',
            'name' => 'LXT 18V',
        ],
        'required_batteries' => 2,
        'required_chargers' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Vezetékes ütvefúró',
        'sku' => 'UTVEFURO-VEZETEKES',
        'inventory_prefix' => 'UTF',
        'description' => 'Vezetékes ütvefúró. Igény esetén hosszabbító külön bérelhető.',
        'price_per_day' => 5000,
        'deposit' => 10000,
        'inventory_quantity' => 2,
    ],

    [
        'category' => 'Létrák, állványok és segédeszközök',
        'name' => 'Talicska',
        'sku' => 'TALICSKA',
        'inventory_prefix' => 'TAL',
        'description' => 'Általános építési és kerti munkákhoz használható talicska.',
        'price_per_day' => 2000,
        'deposit' => 10000,
        'inventory_quantity' => 5,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Vezetékes sarokcsiszoló',
        'sku' => 'SAROKCSISZOLO-VEZETEKES',
        'inventory_prefix' => 'VSC',
        'description' => 'Vezetékes sarokcsiszoló ház körüli és barkácsmunkákhoz.',
        'price_per_day' => 3000,
        'deposit' => 10000,
        'inventory_quantity' => 4,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Akkumulátoros sarokcsiszoló',
        'sku' => 'SAROKCSISZOLO-AKKU',
        'inventory_prefix' => 'ASC',
        'description' => 'Akkumulátoros sarokcsiszoló ház körüli és barkácsmunkákhoz.',
        'price_per_day' => 5000,
        'deposit' => 10000,
        'inventory_quantity' => 1,

        'battery_system' => [
            'manufacturer' => 'Parkside',
            'name' => 'X20V Team',
        ],
        'required_batteries' => 1,
        'required_chargers' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Vezetékes gépi gyalu',
        'sku' => 'GEPI-GYALU-VEZETEKES',
        'inventory_prefix' => 'GYA',
        'description' => 'Vezetékes gépi gyalu 1–22 mm közötti megmunkáláshoz.',
        'price_per_day' => 3000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Excenteres csiszoló',
        'sku' => 'EXCENTERES-CSISZOLO',
        'inventory_prefix' => 'EXC',
        'description' => 'Excenteres csiszoló felületcsiszolási munkákhoz.',
        'price_per_day' => 4000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Vezetékes körfűrész',
        'sku' => 'KORFURESZ-VEZETEKES',
        'inventory_prefix' => 'KOR',
        'description' => 'Vezetékes körfűrész faanyagok vágásához.',
        'price_per_day' => 4000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Létrák, állványok és segédeszközök',
        'name' => '3×11 fokos létra',
        'sku' => 'LETRA-3X11',
        'inventory_prefix' => 'LET',
        'description' => 'Magas, háromrészes 3×11 fokos létra.',
        'price_per_day' => 5000,
        'deposit' => 10000,
        'inventory_quantity' => 2,
    ],

    [
        'category' => 'Létrák, állványok és segédeszközök',
        'name' => 'Állvány 3 méteres munkamagassággal',
        'sku' => 'ALLVANY-3M',
        'inventory_prefix' => 'ALL',
        'description' => 'Mobil állvány körülbelül 3 méteres munkamagassághoz.',
        'price_per_day' => 3000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Létrák, állványok és segédeszközök',
        'name' => 'Fém tartóbak pár',
        'sku' => 'FEM-TARTOBAK-PAR',
        'inventory_prefix' => 'BAK',
        'description' => 'Két darab fém tartóbak együttes bérlése.',
        'price_per_day' => 2000,
        'deposit' => 10000,
        'inventory_quantity' => 1,
    ],

    [
        'category' => 'Barkács- és építőipari kisgépek',
        'name' => 'Betonkeverő 180L',
        'sku' => 'BETONKEVERO-180L',
        'inventory_prefix' => 'BET',
        'description' => '180 literes betonkeverő kisebb ház körüli betonozási munkákhoz.',
        'price_per_day' => 8000,
        'deposit' => 30000,
        'inventory_quantity' => 1,
    ],
];
<?php

namespace Database\Seeders;

use App\Models\BatteryItem;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Services\BookingIssueService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            /*
             * Korábban létrehozott development demo bookingok törlése.
             *
             * Így a seeder többször is biztonságosan futtatható,
             * és nem halmozódnak a tesztfoglalások.
             */
            Booking::query()
                ->whereIn(
                    'customer_email',
                    [
                        'booking-demo@example.com',
                        'booking-pending@example.com',
                    ]
                )
                ->delete();

            /*
             * Olyan akkumulátoros terméket keresünk,
             * amelyhez akkumulátor és töltő is szükséges.
             */
            $product = Product::query()
                ->whereNotNull(
                    'battery_system_id'
                )
                ->where(
                    'required_batteries',
                    '>',
                    0
                )
                ->where(
                    'required_chargers',
                    '>',
                    0
                )
                ->where('active', true)
                ->orderBy('id')
                ->first();

            if (! $product) {
                throw new RuntimeException(
                    'Nem található olyan aktív akkumulátoros termék, '
                    . 'amelyhez akkumulátor és töltő is szükséges.'
                );
            }

            /*
             * ==========================================================
             * ACTIVE DEMO BOOKING
             * ==========================================================
             */

            /*
             * Keresünk hozzá egy szabad fizikai géppéldányt.
             */
            $inventoryItem = InventoryItem::query()
                ->where(
                    'product_id',
                    $product->id
                )
                ->where(
                    'status',
                    'AVAILABLE'
                )
                ->orderBy('id')
                ->first();

            if (! $inventoryItem) {
                throw new RuntimeException(
                    "Nincs elérhető géppéldány ehhez a termékhez: {$product->name}"
                );
            }

            /*
             * A termék akkumulátorrendszeréhez tartozó,
             * elérhető akkumulátorok lekérése.
             */
            $batteries = BatteryItem::query()
                ->where(
                    'battery_system_id',
                    $product->battery_system_id
                )
                ->where(
                    'type',
                    BatteryItem::TYPE_BATTERY
                )
                ->where(
                    'status',
                    BatteryItem::STATUS_AVAILABLE
                )
                ->orderBy('id')
                ->limit(
                    $product->required_batteries
                )
                ->get();

            if (
                $batteries->count()
                !==
                (int) $product->required_batteries
            ) {
                throw new RuntimeException(
                    "Nincs elegendő elérhető akkumulátor ehhez a termékhez: {$product->name}"
                );
            }

            /*
             * A szükséges töltők lekérése.
             */
            $chargers = BatteryItem::query()
                ->where(
                    'battery_system_id',
                    $product->battery_system_id
                )
                ->where(
                    'type',
                    BatteryItem::TYPE_CHARGER
                )
                ->where(
                    'status',
                    BatteryItem::STATUS_AVAILABLE
                )
                ->orderBy('id')
                ->limit(
                    $product->required_chargers
                )
                ->get();

            if (
                $chargers->count()
                !==
                (int) $product->required_chargers
            ) {
                throw new RuntimeException(
                    "Nincs elegendő elérhető töltő ehhez a termékhez: {$product->name}"
                );
            }

            /*
             * Háromnapos ACTIVE demo foglalás.
             *
             * Először CONFIRMED állapotban hozzuk létre,
             * mert innen adható ki a BookingIssueService segítségével.
             */
            $activeBooking = Booking::query()->create([
                'user_id' => null,

                'customer_name' =>
                    'Fejlesztői Teszt Ügyfél',

                'customer_email' =>
                    'booking-demo@example.com',

                'customer_phone' =>
                    '+36301234567',

                'start_date' =>
                    now()->toDateString(),

                'end_date' =>
                    now()
                        ->addDays(2)
                        ->toDateString(),

                'pickup_type' =>
                    'SELF_PICKUP',

                'planned_pickup_at' =>
                    now()
                        ->setTime(9, 0),

                'status' =>
                    'CONFIRMED',

                'customer_note' =>
                    'Development ACTIVE demo foglalás.',

                'admin_note' =>
                    null,
            ]);

            /*
             * 3 naptári nap:
             *
             * kezdőnap + következő két nap.
             */
            $rentalDays = 3;

            $pricePerDay =
                (float) $product->price_per_day;

            $depositPerItem =
                (float) $product->deposit;

            BookingItem::query()->create([
                'booking_id' =>
                    $activeBooking->id,

                'product_id' =>
                    $product->id,

                'inventory_item_id' =>
                    null,

                'quantity' =>
                    1,

                'price_per_day' =>
                    $pricePerDay,

                'deposit_per_item' =>
                    $depositPerItem,

                'rental_days' =>
                    $rentalDays,

                'rental_subtotal' =>
                    $pricePerDay
                    * $rentalDays,

                'deposit_subtotal' =>
                    $depositPerItem,
            ]);

            /*
             * A konkrét akkumulátorok és töltők azonosítóinak
             * összeállítása.
             */
            $batteryItemIds = $batteries
                ->pluck('id')
                ->merge(
                    $chargers->pluck('id')
                )
                ->values()
                ->all();

            /*
             * A valódi kiadási service futtatása.
             *
             * Ez fogja:
             *
             * - létrehozni a gép allocationt,
             * - RENTED állapotba tenni a gépet,
             * - létrehozni a battery allocation rekordokat,
             * - RENTED állapotba tenni az akkukat/töltőket,
             * - létrehozni a szükséges státusztörténeteket,
             * - ACTIVE állapotba tenni a bookingot.
             */
            /** @var BookingIssueService $bookingIssueService */
            $bookingIssueService = app(
                BookingIssueService::class
            );

            $bookingIssueService->issue(
                booking: $activeBooking,
                inventoryItemIds: [
                    $inventoryItem->id,
                ],
                batteryAllocations: [
                    [
                        'inventory_item_id' =>
                            $inventoryItem->id,

                        'battery_item_ids' =>
                            $batteryItemIds,
                    ],
                ],
            );

            /*
             * ==========================================================
             * PENDING DEMO BOOKING
             * ==========================================================
             *
             * Ezt szándékosan nem adjuk ki és nem hagyjuk jóvá.
             * Az admin approval/rejection frontend tesztelésére szolgál.
             */

            $pendingBooking = Booking::query()->create([
                'user_id' => null,

                'customer_name' =>
                    'Jóváhagyásra Váró Ügyfél',

                'customer_email' =>
                    'booking-pending@example.com',

                'customer_phone' =>
                    '+36309876543',

                'start_date' =>
                    now()
                        ->addDays(30)
                        ->toDateString(),

                'end_date' =>
                    now()
                        ->addDays(32)
                        ->toDateString(),

                'pickup_type' =>
                    'SELF_PICKUP',

                'planned_pickup_at' =>
                    now()
                        ->addDays(30)
                        ->setTime(10, 0),

                'status' =>
                    'PENDING',

                'customer_note' =>
                    'Development PENDING demo foglalás.',

                'admin_note' =>
                    null,
            ]);

            BookingItem::query()->create([
                'booking_id' =>
                    $pendingBooking->id,

                'product_id' =>
                    $product->id,

                'inventory_item_id' =>
                    null,

                'quantity' =>
                    1,

                'price_per_day' =>
                    $pricePerDay,

                'deposit_per_item' =>
                    $depositPerItem,

                'rental_days' =>
                    $rentalDays,

                'rental_subtotal' =>
                    $pricePerDay
                    * $rentalDays,

                'deposit_subtotal' =>
                    $depositPerItem,
            ]);

            /*
             * Seeder visszajelzés.
             */
            $this->command?->info(
                "ACTIVE development booking létrehozva. ID: {$activeBooking->id}"
            );

            $this->command?->info(
                "PENDING development booking létrehozva. ID: {$pendingBooking->id}"
            );

            $this->command?->info(
                "Termék: {$product->name}"
            );

            $this->command?->info(
                "ACTIVE booking gép: {$inventoryItem->inventory_code}"
            );

            $this->command?->info(
                'ACTIVE booking akkumulátor/töltő: '
                . $batteries
                    ->concat($chargers)
                    ->pluck('inventory_code')
                    ->implode(', ')
            );
        });
    }
}
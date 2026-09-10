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
             * Korábban létrehozott development demo booking törlése.
             *
             * Így a seeder többször is biztonságosan futtatható,
             * és nem halmozódnak a tesztfoglalások.
             */
            Booking::query()
                ->where(
                    'customer_email',
                    'booking-demo@example.com'
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
             * Háromnapos demo foglalás.
             *
             * Azért CONFIRMED állapotban hozzuk létre,
             * mert innen adható ki a BookingIssueService segítségével.
             */
            $booking = Booking::query()->create([
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
                    'Development demo foglalás.',

                'admin_note' =>
                    null,
            ]);

            /*
             * 3 naptári nap:
             *
             * kezdőnap + következő két nap.
             */
            $rentalDays = 3;

            $pricePerDay = (float) $product->price_per_day;
            $depositPerItem = (float) $product->deposit;

            $bookingItem = BookingItem::query()->create([
                'booking_id' =>
                    $booking->id,

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
                booking: $booking,
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

            $this->command?->info(
                "Development booking létrehozva. ID: {$booking->id}"
            );

            $this->command?->info(
                "Termék: {$product->name}"
            );

            $this->command?->info(
                "Gép: {$inventoryItem->inventory_code}"
            );

            $this->command?->info(
                'Akkumulátor/töltő: '
                . $batteries
                    ->concat($chargers)
                    ->pluck('inventory_code')
                    ->implode(', ')
            );
        });
    }
}
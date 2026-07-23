# Kisgép Kölcsönző - Database Design

## 1. Áttekintés

A rendszer célja egy kisgép-kölcsönző vállalkozás teljes körű támogatása.

A rendszer kezeli:

* gépkategóriákat
* géptípusokat
* konkrét gépeket
* felhasználókat
* vendég foglalásokat
* foglalásokat
* fizetéseket
* értékeléseket
* email értesítéseket
* házhoz szállítást
* admin jóváhagyást

---

## 2. Táblák

### users

Felhasználók és adminisztrátorok.

| Mező       | Típus     | Leírás             |
| ---------- | --------- | ------------------ |
| id         | bigint    | Azonosító          |
| name       | varchar   | Teljes név         |
| email      | varchar   | Email cím          |
| password   | varchar   | Titkosított jelszó |
| phone      | varchar   | Telefonszám        |
| address    | varchar   | Cím                |
| role       | enum      | customer/admin     |
| created_at | timestamp | Létrehozás dátuma  |
| updated_at | timestamp | Módosítás dátuma   |

---

### categories

Gépkategóriák.

Példák:

* Betonkeverők
* Fúrógépek
* Láncfűrészek
* Fűnyírók
* Döngölők

| Mező        | Típus     | Leírás            |
| ----------- | --------- | ----------------- |
| id          | bigint    | Azonosító         |
| name        | varchar   | Kategória neve    |
| description | text      | Rövid leírás      |
| created_at  | timestamp | Létrehozás dátuma |
| updated_at  | timestamp | Módosítás dátuma  |

---

### products

A géptípusokat tartalmazza.

Példa:

* Betonkeverő 180L
* Husqvarna láncfűrész

| Mező          | Típus     | Leírás              |
| ------------- | --------- | ------------------- |
| id            | bigint    | Azonosító           |
| category_id   | bigint    | Kategória azonosító |
| name          | varchar   | Gép neve            |
| description   | text      | Részletes leírás    |
| price_per_day | decimal   | Napi bérleti díj    |
| deposit       | decimal   | Kaució              |
| active        | boolean   | Aktív-e             |
| created_at    | timestamp | Létrehozás dátuma   |
| updated_at    | timestamp | Módosítás dátuma    |

---

### inventory_items

A tényleges, fizikailag létező gépek.

Példák:

* Betonkeverő #001
* Betonkeverő #002
* Betonkeverő #003

| Mező          | Típus     | Leírás            |
| ------------- | --------- | ----------------- |
| id            | bigint    | Azonosító         |
| product_id    | bigint    | Géptípus          |
| serial_number | varchar   | Egyedi azonosító  |
| status        | enum      | Gép állapota      |
| created_at    | timestamp | Létrehozás dátuma |
| updated_at    | timestamp | Módosítás dátuma  |

#### Lehetséges státuszok

* AVAILABLE
* RENTED
* MAINTENANCE
* DAMAGED
* INACTIVE

---

### bookings

Egy foglalás több gépet is tartalmazhat.

| Mező             | Típus            | Leírás                  |
| ---------------- | ---------------- | ----------------------- |
| id               | bigint           | Azonosító               |
| user_id          | bigint nullable  | Regisztrált felhasználó |
| guest_name       | varchar          | Vendég neve             |
| guest_email      | varchar          | Vendég email címe       |
| guest_phone      | varchar          | Vendég telefonszáma     |
| start_date       | date             | Kölcsönzés kezdete      |
| end_date         | date             | Kölcsönzés vége         |
| pickup_type      | enum             | Átvétel módja           |
| pickup_time      | time             | Tervezett érkezés ideje |
| delivery_address | varchar nullable | Szállítási cím          |
| delivery_lat     | decimal nullable | Szélességi fok          |
| delivery_lng     | decimal nullable | Hosszúsági fok          |
| status           | enum             | Foglalás állapota       |
| created_at       | timestamp        | Létrehozás dátuma       |
| updated_at       | timestamp        | Módosítás dátuma        |

#### Pickup Type

* SELF_PICKUP
* DELIVERY

#### Booking Status

* PENDING
* CONFIRMED
* CANCELLED
* COMPLETED

---

### booking_items

A foglaláshoz tartozó gépek.

| Mező              | Típus     | Leírás               |
| ----------------- | --------- | -------------------- |
| id                | bigint    | Azonosító            |
| booking_id        | bigint    | Foglalás             |
| inventory_item_id | bigint    | Konkrét gép          |
| price_per_day     | decimal   | Foglaláskori napi ár |
| deposit_amount    | decimal   | Foglaláskori kaució  |
| days              | integer   | Napok száma          |
| subtotal          | decimal   | Részösszeg           |
| created_at        | timestamp | Létrehozás dátuma    |
| updated_at        | timestamp | Módosítás dátuma     |

---

### reviews

Csak bejelentkezett felhasználók írhatnak értékelést.

| Mező       | Típus     | Leírás            |
| ---------- | --------- | ----------------- |
| id         | bigint    | Azonosító         |
| user_id    | bigint    | Felhasználó       |
| booking_id | bigint    | Foglalás          |
| product_id | bigint    | Géptípus          |
| rating     | integer   | 1-5 értékelés     |
| comment    | text      | Szöveges vélemény |
| approved   | boolean   | Admin jóváhagyás  |
| created_at | timestamp | Létrehozás dátuma |
| updated_at | timestamp | Módosítás dátuma  |

---

### payments

A foglalásokhoz kapcsolódó fizetések.

| Mező           | Típus              | Leírás            |
| -------------- | ------------------ | ----------------- |
| id             | bigint             | Azonosító         |
| booking_id     | bigint             | Foglalás          |
| amount         | decimal            | Fizetendő összeg  |
| payment_method | varchar            | Fizetési mód      |
| status         | enum               | Fizetési állapot  |
| paid_at        | timestamp nullable | Fizetés ideje     |
| created_at     | timestamp          | Létrehozás dátuma |
| updated_at     | timestamp          | Módosítás dátuma  |

#### Payment Status

* PENDING
* PAID
* FAILED
* REFUNDED

---

### notifications

Email és rendszerértesítések naplózása.

| Mező       | Típus           | Leírás            |
| ---------- | --------------- | ----------------- |
| id         | bigint          | Azonosító         |
| user_id    | bigint nullable | Felhasználó       |
| booking_id | bigint nullable | Foglalás          |
| type       | varchar         | Értesítés típusa  |
| status     | enum            | Állapot           |
| sent_at    | timestamp       | Küldés ideje      |
| created_at | timestamp       | Létrehozás dátuma |
| updated_at | timestamp       | Módosítás dátuma  |

---

### settings

Alkalmazás szintű beállítások.

| Mező               | Típus     | Leírás               |
| ------------------ | --------- | -------------------- |
| id                 | bigint    | Azonosító            |
| company_name       | varchar   | Cég neve             |
| address            | varchar   | Telephely címe       |
| latitude           | decimal   | Telephely koordináta |
| longitude          | decimal   | Telephely koordináta |
| delivery_radius_km | integer   | Szállítási körzet    |
| phone              | varchar   | Telefonszám          |
| email              | varchar   | Kapcsolattartó email |
| created_at         | timestamp | Létrehozás dátuma    |
| updated_at         | timestamp | Módosítás dátuma     |

---

## 3. Kapcsolatok

```text
Category
    |
    | 1:N
    |
Product
    |
    | 1:N
    |
InventoryItem


User
    |
    | 1:N
    |
Booking
    |
    | 1:N
    |
BookingItem
    |
    | N:1
    |
InventoryItem


Booking
    |
    | 1:N
    |
Payment


User
    |
    | 1:N
    |
Review
    |
    | N:1
    |
Product
```

---

## 4. Üzleti szabályok

1. Egy foglalás több gépet is tartalmazhat.
2. Csak szabad (`AVAILABLE`) gép foglalható.
3. Egy gép nem foglalható két átfedő időszakra.
4. Vendégként is lehet foglalni.
5. Értékelést csak regisztrált és bejelentkezett felhasználó írhat.
6. Értékelést csak lezárt (`COMPLETED`) foglalás után lehet írni.
7. Házhoz szállítás csak a telephely 30 km-es körzetében érhető el.
8. A foglalások admin jóváhagyást igényelnek.
9. Fizetés csak jóváhagyott foglalás után indítható.
10. A foglaláskor érvényes árakat és kauciót mindig el kell menteni.

---

## 5. Jövőbeli funkciók

* Online fizetés (Stripe/SimplePay)
* PDF szerződés generálás
* Automatikus emlékeztető emailek
* Törzsvásárlói kedvezmények
* Kupon rendszer
* Admin statisztikák
* Karbantartási előzmények
* Google Calendar szinkronizáció
* Automatikus számlázás

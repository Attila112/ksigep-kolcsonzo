# Development Database

A fejlesztői adatbázis kizárólag seederekből épül fel.

Seederek:

- CategorySeeder
- ProductSeeder
- WorkTypeSeeder
- InventorySeeder

Új termék felvétele:

1. database/development-data/products.php
2. ProductSeeder automatikusan létrehozza
3. InventorySeeder automatikusan létrehozza a géppéldányokat
4. WorkTypeSeeder hozzárendeli a megfelelő munkatípusokhoz

Frissítés:

php artisan migrate:fresh --seed
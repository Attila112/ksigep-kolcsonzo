# Kisgép-kölcsönző rendszer – Roadmap

## Projekt célja

A cél nem egy egyszerű weboldal elkészítése, hanem egy teljes körű kisgép-kölcsönző üzemeltetési rendszer létrehozása.

A rendszernek egyszerre kell kiszolgálnia:

* a nyilvános weboldalt;
* az ügyfelek online foglalását;
* az adminisztrációt;
* a készletkezelést;
* a napi működést;
* a későbbi mobilos és QR-kódos bővítéseket.

---

# Alapelvek

A projekt minden fejlesztése az alábbi elvek szerint történik.

* Egyszerű használhatóság.
* Stabil architektúra.
* Tiszta kód.
* Teljes tesztelhetőség.
* Dokumentált üzleti szabályok.
* Többnyelvűség támogatása.
* Könnyű bővíthetőség.

A rendszernek éveken keresztül fejleszthetőnek kell maradnia.

---

# Verzióterv

## ✅ v0.1.0 – Backend Foundation

Elkészült.

Tartalom:

* Laravel API
* Auth alapok
* Category
* Product
* Inventory
* Booking
* Review
* Tesztek
* Projekt architektúra

---

## ✅ v0.2.0 – Booking & Inventory Engine

Elkészült.

Tartalom:

* Foglalási folyamat
* Jóváhagyás
* Kiadás
* Inventory hozzárendelés
* Részleges visszavétel
* Inventory státuszkezelés
* Inventory státusztörténet
* Admin műveletek
* Teljes tesztlefedettség

---

## ✅ v0.3.0 – Frontend Foundation

Elkészült.

Tartalom:

* Next.js projekt
* API kapcsolat
* Core architektúra
* Product komponensek
* Többnyelvű alap
* Fordítási struktúra
* Újrafelhasználható frontend alapok

---

# 🚧 v0.4.0 – Public Website

Következő cél.

Feladatok:

* UI alapkomponensek
* Header
* Footer
* Navigáció
* Product Card
* Product Details
* Kategóriaoldalak
* Keresés
* Szűrés
* Képgaléria
* Reszponzív felület

---

# 📅 v0.5.0 – Customer Area

Tervezett.

Feladatok:

* Regisztráció
* Bejelentkezés
* Profil
* Saját foglalások
* Foglalás részletei
* Jelszó módosítás
* Értékelések

---

# 📅 v0.6.0 – Admin Panel

Tervezett.

Feladatok:

* Dashboard
* Termékkezelés
* Inventory kezelés
* Foglaláskezelés
* Jóváhagyás
* Kiadás
* Visszavétel
* Szervizkezelés
* Felhasználók

---

# 📅 v0.7.0 – QR Workflow

Tervezett.

Feladatok:

* QR-kód generálása
* QR beolvasás mobil böngészőből
* Gyors kiadás
* Gyors visszavétel
* Inventory azonosítás

---

# 📅 v0.8.0 – Reporting

Tervezett.

Feladatok:

* Dashboard statisztikák
* Bevételi riportok
* Kihasználtság
* Inventory előélet
* Export Excelbe
* Export PDF-be

---

# 📅 v0.9.0 – Business Features

Tervezett.

Lehetséges funkciók:

* Email értesítések
* Automatikus szerződés
* Digitális aláírás
* Kaució kezelés
* Számlázó integráció
* Naptár nézet

---

# 🚀 v1.0.0 – Production Release

Első éles kiadás.

Elvárt állapot:

* Stabil rendszer
* Dokumentáció elkészült
* Tesztek zöldek
* Biztonságos éles telepítés
* Biztonsági mentések
* Naplózás
* Monitoring

---

# Hosszú távú ötletek

Nem részei az első éles verziónak, de később megvalósíthatók.

* Mobilalkalmazás
* Vonalkód támogatás
* Push értesítések
* SMS értesítések
* Több telephely kezelése
* Több raktár
* Több cég kezelése
* API külső rendszerekhez
* Online fizetés
* Dinamikus árazás
* Hűségprogram

---

# Fejlesztési folyamat

Minden új funkció ugyanazt a folyamatot követi.

1. Üzleti működés átbeszélése.
2. Dokumentáció frissítése.
3. Tesztek megírása.
4. Fejlesztés.
5. Kipróbálás.
6. Commit.
7. Verzió kiadása (ha mérföldkő).

---

# Projekt küldetése

A projekt célja egy olyan rendszer elkészítése, amely egy valódi kisgép-kölcsönző napi működését képes támogatni.

A rendszernek egyszerűnek kell lennie a felhasználók számára, ugyanakkor minden szükséges adminisztratív és üzemeltetési funkciót biztosítania kell.

A fejlesztés során mindig a hosszú távú fenntarthatóság, a tiszta architektúra és a valós üzleti igények élveznek elsőbbséget.

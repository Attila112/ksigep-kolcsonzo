# Business Rules

## A dokumentum célja

Ez a dokumentum a kisgép-kölcsönző rendszer üzleti szabályait tartalmazza.

A célja, hogy minden fontos üzleti döntés egy helyen legyen dokumentálva, és ne csak a kódban vagy a fejlesztők emlékezetében létezzen.

A dokumentum folyamatosan bővül a fejlesztés során. Minden új funkció megvalósítása előtt az üzleti szabályokat szükség esetén frissítjük.

---

# Alapelvek

## AR-001 – Egyszerű használhatóság

A rendszer elsődleges célja, hogy a felhasználók és az adminisztrátorok számára is egyszerűen és gyorsan használható legyen.

Az összetett üzleti logika a háttérben működik, a felhasználó csak a számára szükséges műveleteket lássa.

---

## AR-002 – Mobilközpontú működés

A rendszernek számítógépen, tableten és mobiltelefonon egyaránt teljes értékűen használhatónak kell lennie.

Minden új felület tervezésekor ezt alapkövetelményként kell kezelni.

---

## AR-003 – Egyetlen központi rendszer

A nyilvános weboldal, az adminisztrációs felület és a későbbi QR-kódos vagy mobilos funkciók ugyanazt a központi Laravel API-t és adatbázist használják.

Nem készülnek külön adatkezelő rendszerek.

---

## AR-004 – Valós üzleti működés

A rendszer nem bemutató alkalmazásnak készül.

Minden funkciónak egy valódi kisgép-kölcsönző napi működését kell támogatnia.

---

## AR-005 – Hosszú távú bővíthetőség

Az első verzió csak a valóban szükséges funkciókat tartalmazza.

A rendszer architektúráját azonban úgy kell kialakítani, hogy a későbbi bővítések (például több telephely, céges ügyfelek vagy mobilalkalmazás) jelentős átírás nélkül megvalósíthatók legyenek.

---

## AR-006 – Kétféle böngészési mód

A rendszernek kétféle módon kell segítenie a felhasználót a megfelelő gép kiválasztásában.

### Munkafolyamat alapú böngészés

Azoknak a felhasználóknak készült, akik nem tudják pontosan, milyen gépre van szükségük.

A rendszer a kívánt feladat alapján ajánl megfelelő gépeket és tartozékokat.

Példák:

* Kert rendbetétele
* Favágás
* Takarítás
* Festés
* Barkácsolás
* Betonozás

### Hagyományos termékböngészés

Azoknak a felhasználóknak készült, akik már tudják, milyen gépet szeretnének bérelni.

Számukra elérhető:

* kategóriák szerinti böngészés;
* lista nézet;
* kereső;
* szűrők.

Mindkét megközelítés ugyanazt a termékkészletet használja.

A két felület nem külön rendszer, csak két különböző nézete ugyanannak a kínálatnak.

---

# Vállalkozás

## BR-001 – Telephely

Az első verzió egyetlen telephelyet kezel.

A rendszer később bővíthető több telephely támogatására, de ez nem része az első kiadásnak.

---

## BR-002 – Működési terület

Az első verzió Magyarországon működik.

A rendszer pénzneme magyar forint (HUF).

Az időzóna Europe/Budapest.

A felület azonban többnyelvű, hogy a Magyarországon élő külföldi ügyfelek is használni tudják.

---

## BR-003 – Házhoz szállítás

A vállalkozás házhoz szállítást is biztosít.

Az első verzióban a kiszállítás legfeljebb 30 kilométeres távolságon belül érhető el.

A kiszállítás pontos díjszabályai később kerülnek meghatározásra.

---

# Fejlesztési szabály

Minden új funkció fejlesztése előtt az alábbi folyamatot követjük:

1. Az üzleti működés átbeszélése.
2. A Business Rules dokumentum frissítése (ha szükséges).
3. A tesztek megírása.
4. A funkció fejlesztése.
5. Kipróbálás és ellenőrzés.
6. Commit.
7. Verziófrissítés, ha mérföldkő készült el.

---

# Nyitott kérdések

Az ide kerülő témák még nem rendelkeznek végleges üzleti döntéssel.

Például:

* online fizetés;
* kaució elektronikus kezelése;
* késedelmes visszahozatal;
* foglalás hosszabbítása;
* több telephely;
* céges ügyfelek;
* mobilalkalmazás;
* QR-kódos munkafolyamatok.


Az akkumulátoros gépekhez szükséges akkumulátorok és töltők automatikusan a bérlés részét képezik. Az ügyfél ezeket nem választja külön. A termék meghatározza a szükséges akkumulátorrendszert és mennyiséget, a konkrét akkumulátor- és töltőpéldányokat pedig az admin a tényleges kiadáskor rendeli hozzá. Egy kompatibilis akkumulátor vagy töltő nincs fixen egy géphez kötve, több azonos rendszerű gép között szabadon használható.

BR – Product és Inventory szétválasztása: A Product a bérelhető terméktípust írja le, minden fizikai géppéldány külön InventoryItem. Minden InventoryItem saját azonosítóval, sorozatszámmal, státusszal és teljes előélettel rendelkezik.

BR – Adminból kezelhető működés: A vállalkozás normál napi működéséhez szükséges adatmódosításokat az adminfelületről kell elvégezni. Új termék, új géppéldány, ár, kaució, státusz, kép és más üzleti adat módosítása nem igényelhet kódmódosítást.
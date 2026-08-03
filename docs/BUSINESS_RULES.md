# Üzleti szabályok

## Foglalás

- Vendég és regisztrált felhasználó is küldhet foglalási kérelmet.
- Az új foglalás kezdeti státusza `PENDING`.
- A `PENDING`, `CONFIRMED` és `ACTIVE` foglalások csökkentik az elérhető kapacitást.
- A konkrét fizikai géppéldány jóváhagyáskor még nem kerül kiválasztásra.
- A konkrét `inventory_item` csak a tényleges kiadáskor kerül hozzárendelésre.
- Csak `PENDING` foglalás hagyható jóvá vagy utasítható el.
- Csak `CONFIRMED` foglalás adható ki.
- A foglalás `ACTIVE` lesz, amikor a konkrét gépeket kiadták.
- A foglalás csak akkor lesz `COMPLETED`, amikor minden kiadott gépet visszavettek.
- Részleges visszavétel támogatott.

## Inventory

- Minden fizikai gép külön inventory rekord.
- Egy gép lehetséges állapotai:
  - `AVAILABLE`
  - `RENTED`
  - `INSPECTION`
  - `MAINTENANCE`
  - `DAMAGED`
  - `INACTIVE`
- Csak `AVAILABLE` állapotú gép adható ki.
- Kiadáskor a gép `AVAILABLE → RENTED` állapotba kerül.
- Visszavételkor a gép `RENTED → INSPECTION` állapotba kerül.
- A visszavett gép nem kerül automatikusan `AVAILABLE` állapotba.
- Az admin dönt az ellenőrzés után a következő állapotról.
- `RENTED` állapotot az admin nem módosíthat kézzel.
- Minden státuszváltozás bekerül az inventory státusztörténetébe.

## Árazás

- Az árakat mindig a backend számítja.
- A frontend által küldött összegek nem tekinthetők hitelesnek.
- A foglalási tételbe az aktuális napi ár és kaució pillanatképe kerül.
- A kezdő- és zárónap is bérleti napnak számít.

## Adminfelület

- Admin funkciót csak `ADMIN` szerepkörű, aktív felhasználó érhet el.
- A felület csak az aktuális státuszban megengedett műveleteket jelenítse meg.
- A kritikus módosításokat naplózni kell.
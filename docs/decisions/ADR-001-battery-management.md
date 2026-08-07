# ADR-001 - Battery Management

## Státusz

Accepted

---

## Probléma

Több akkumulátoros gépet fogunk üzemeltetni.

Egy géphez mindig jár akkumulátor és töltő.

Az ügyfél azonban nem választja ki ezeket.

A vállalkozásnak viszont tudnia kell,
hogy melyik konkrét akkumulátor és töltő
melyik kiadáshoz került.

---

## Döntés

Az akkumulátorok és töltők
külön inventory elemek.

Nem tartoznak fixen egy géphez.

Az admin rendeli őket a géphez
a tényleges kiadáskor.

Az ügyfél ezt nem látja.

---

## Indoklás

• egyszerű ügyfélélmény

• teljes nyomon követhetőség

• egy akkumulátor több géppel használható

• QR-kódos kiadás előkészítése

---

## Következmények

Új entitások:

BatterySystem

BatteryInventory

A Product meghatározza:

- battery_system

- required_battery_quantity

- required_charger_quantity
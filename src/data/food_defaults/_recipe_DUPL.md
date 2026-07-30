# Recipe: make one food default file

Instructions for an assistant that creates a new file in `data/food_defaults`.
Everything needed is in here - follow it step by step and do not skip the checks
at the end. The format is in `_blank_ai.yml`, finished examples are `Lentils.yml`
and `Butter.yml`, the foods still without defaults are in the gaps file next to
this one.


The task you are given
----------------------------------------------------------

You get one line like this:

```
Type: Avocado
Foods: Avocado, Avocado gr
```

`Type` is the file name to create (`data/food_defaults/Avocado.yml`).
`Foods` are the foods in `data/bundles/Default_<user>/foods` that will use it.

Produce two things:

1. the new `data/food_defaults/<Type>.yml`
2. the line `type:              <Type>` added to each listed food file

Nothing else. Do not touch other files, do not change values inside the food
files - the food's own values always win over the defaults.


Step 1: look at the foods first
----------------------------------------------------------

Open each listed food in `data/bundles/Default_<user>/foods` and note

- `calories` and `nutritionalValues` - they tell you the **state** of the food
- `productName`, `comment`, `weight`

The state decides which source entry is right. This is the most common mistake:

- raw vegetable ~20-60 kcal, cooked/canned legumes ~90-120 kcal, dry legumes ~350 kcal
- raw nuts ~550-650 kcal, oil ~800-900 kcal

Example: the lentil foods have 89 and 92 kcal per 100 g, so the source had to be
**cooked** lentils. A dry entry with 350 kcal would have been wrong.

If the foods disagree with each other, pick the state most of them share and say
so in the header comment.


Step 2: find the source entry
----------------------------------------------------------

Primary source is USDA FoodData Central, <https://fdc.nal.usda.gov>.

Search: <https://api.nal.usda.gov/fdc/v1/foods/search?api_key=DEMO_KEY&query=lentils&dataType=Foundation,SR%20Legacy>
One entry: <https://api.nal.usda.gov/fdc/v1/food/172421?api_key=DEMO_KEY>

Choose like this:

- the entry must match the state from step 1 (raw / cooked / canned / dry)
- prefer the entry with the **most nutrients**; `Foundation` is analytical but
  often sparse, `SR Legacy` usually carries vitamins, amino acids and fatty acids
- `Branded` entries are label data - do not use them
- if two entries fit, use the fuller one and cite the other as an unused
  alternative in `sources`

Only if USDA has nothing usable, use one of these instead, in this order:
Ciqual (France), CoFID (UK), Frida (Denmark). **One database per food** - never
mix single values from different databases. Taking single fields from a second
*USDA* entry is allowed when the main entry lacks them, then say per field where
it came from (see `Butter.yml`).


Step 3: fill the template
----------------------------------------------------------

Copy `_blank_ai.yml` and fill it. Keep its field order, its comment column and
its blank lines. Leave `acceptable` and `comment` empty.

Write a header comment at the top of the file: which state the entry has, which
source id is used for what, and what the source does not carry.

Rules for values:

- per 100 g, no units in the value, unit belongs in the comment
- a value the source does not have is `0` with a comment `# not in source`
- never invent, estimate or copy from memory - every number comes from the source
- if the source has several values for one thing, take one and note the others:
  - **calories**: prefer `Energy (Atwater Specific Factors)`, then
    `Energy (Atwater General Factors)`, then plain `Energy` in kcal
  - **fat**: prefer `Total lipid (fat)` over `fat (NLEA)`
- put the source number in the comment when you converted it, e.g. `# 445.8 mg -> g`


Step 4: units - read this twice
----------------------------------------------------------

Both bugs ever found in these files were unit slips. The group comment is not
enough, use this table.

| yml field | unit in the yml | source unit | what to do |
|---|---|---|---|
| `calories` | kcal | kcal | take as is |
| `nutritionalValues.*` | g | g | take as is |
| `nutritionalValues.salt` | g | Sodium in mg | mg / 1000 |
| `fattyAcids.Alpha-linolenic acid` | g | g | take as is |
| `fattyAcids.Linoleic acid` | g | g | take as is |
| `fattyAcids.Eicosapentaenoic acid` | **mg** | g | g x 1000 |
| `fattyAcids.Docosahexaenoic acid` | **mg** | g | g x 1000 |
| `carbs.Fibre` | g | g | take as is |
| `aminoAcids.*` | **mg** | g | g x 1000 |
| `vitamins.*` | **mg** | mg or µg | µg / 1000, mg as is |
| `minerals.Salt` | **g** | Sodium in mg | mg / 1000 |
| `minerals.Potassium` | **g** | mg | mg / 1000 |
| `minerals.Calcium` | **g** | mg | mg / 1000 |
| `minerals.` all others | **mg** | mg or µg | µg / 1000, mg as is |
| `misc.water` | g | g | take as is |

So in `minerals` only **Salt, Potassium and Calcium are grams**, the other
eleven are milligrams. Writing `Magnesium: 0.0016` instead of `1.6` is the exact
mistake that was in `Butter.yml`, and `Potassium: 0.0446` instead of `0.4458`
the one in `Poato.yml`.

Names that differ between source and yml:

| source | yml |
|---|---|
| `Protein` | `nutritionalValues.amino` |
| `Carbohydrate, by difference` | `nutritionalValues.carbs` |
| `Fiber, total dietary` | `nutritionalValues.fibre` and `carbs.Fibre` |
| `Sodium, Na` | `nutritionalValues.salt` and `minerals.Salt` |
| `PUFA 18:3 n-3 c,c,c (ALA)` | `fattyAcids.Alpha-linolenic acid` |
| `PUFA 18:2 n-6 c,c` | `fattyAcids.Linoleic acid` |
| `PUFA 20:5 n-3 (EPA)` | `fattyAcids.Eicosapentaenoic acid` |
| `PUFA 22:6 n-3 (DHA)` | `fattyAcids.Docosahexaenoic acid` |
| `Vitamin A, RAE` | `vitamins.Vitamin A` |
| `Vitamin E (alpha-tocopherol)` | `vitamins.Vitamin E` |
| `Vitamin K (phylloquinone)` | `vitamins.Vitamin K` |
| `Thiamin` / `Riboflavin` / `Niacin` | `Thiamin B1` / `Riboflavin B2` / `Niacin B3` |
| `Folate, total` | `vitamins.Folate B9` |

The key is `Fibre`, **not** `Fiber`. A wrong key is silently ignored by the app,
the value simply never shows up.

Amino acids: the source has 18, the yml has 9. Fill those 9, leave the rest out.
Cystine and tyrosine are not in the yml - do not add them.


Step 5: sources and date
----------------------------------------------------------

List every entry you looked at, the used one first:

```yml
sources:

  - title:   Default source = Lentils, mature seeds, cooked, boiled, without salt (SR Legacy)
    comment: most complete panel of the lentil entries
    source:  https://fdc.nal.usda.gov/food-details/172421/nutrients

  - title:   Alternative = Lentils, from canned (FNDDS, unused)
    comment: closer to the tinned products, but no amino acids
    source:  https://fdc.nal.usda.gov/food-details/2707426/nutrients

lastUpd: 2026-07-30
```

The `food-details/<id>/nutrients` url form is required - the verification tool
reads the ids out of it.

Careful with yml: a value containing `:` must be quoted, otherwise the file does
not parse (`Broccoli_Grok_2505.yml` has that error).


Step 6: wire the foods
----------------------------------------------------------

Add to each listed food file, as the first line of the file or directly above
`productName`:

```yml
type:              Lentils
```

Value = the file name without `.yml`, spelling and case exactly. Without this
line the new file is never read. Change nothing else in the food.


Step 7: check before you answer
----------------------------------------------------------

Go through this list and fix what fails:

1. every field of `_blank_ai.yml` is present, none added, none renamed
2. `carbs.Fibre` spelled with `re`, same value as `nutritionalValues.fibre`
3. `minerals`: only Salt, Potassium, Calcium in grams, the other eleven in mg
4. amino acids in mg (three digits for most foods, not 0.x)
5. every value traces to the source; converted ones carry the source number in
   the comment
6. `sources` has at least the used entry with a `food-details/<id>/nutrients` url
7. `lastUpd` is today
8. each listed food has its `type` line
9. the file parses as yml

Then say which entry you used, and which values the source did not have.

If a verification is possible in your environment, run it and paste the output:

```
php src/tools/data_verification/verify_food_defaults.php --file=<Type>
```

It reports unknown keys, fields left out, and every value that does not match
the cited source (a unit slip shows up as "10x/100x/1000x too small"). Anything
it flags must either be fixed or explained in your answer.

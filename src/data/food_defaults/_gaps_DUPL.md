# Food defaults - gaps

Pure foods in the bundle that have no default values yet, see [README](README.md).

A bundle food links to its defaults through its `type` field: `type: Butter` merges
`food_defaults/Butter.yml` under the food's own values (see models/CombinedModel.php).
Foods with mixed ingredients (bread, ready meals, bars, mixes) stay out of this list -
there are no reference values for them.


Status
----------------------------------------------------------

- 12 yml files in food_defaults, 10 of them referenced by a food
- **every `type` currently in use has a default file** - no broken links, so the gap is
  not in the linking but in the foods that carry no `type` at all
- 195 of the 225 bundle foods (variants expanded) have no `type`; of those 43 are pure
  foods (27 types, listed below), 16 are borderline, the remaining 136 are mixed products

The pure foods currently take their nutrient panel from whatever was at hand - "web",
"pack", in some cases "ChatGPT (vals on websites differ)" - and only carry the few
values printed on the package. They are the ones that gain a full nutrient panel from a
reference table.


Sources
----------------------------------------------------------

Primary: <https://fdc.nal.usda.gov> (as used for Butter, Almonds, Chicken ...).

Fallbacks, for foods that are missing in the primary source only - the European tables
also fit the European products better where they exist:

1. Ciqual (ANSES, France) - English UI, downloadable, full nutrient panel
2. CoFID (McCance & Widdowson, UK)
3. Frida (Denmark) - clean per-100g data

One database per food, no single values picked from different databases. Combining two
entries *inside* one database stays allowed where the better entry is sparse - Butter.yml
documents that case per field.


Gaps: pure foods without defaults
----------------------------------------------------------

"Foods" = the bundle foods that would get `type: <proposed type>`.

### Vegetables and fruit

| Proposed type | Foods | Note |
|---|---|---|
| Avocado | Avocado, Avocado gr | current values are from ChatGPT |
| Banana | Banane | |
| Kiwi | Kiwi 6 St | food notes "just some values from misc source, improve" |
| Mushrooms | Champ N, Champ mini ganz R | white / button mushrooms, raw |
| Green beans | Green beans R, Gr beans fein R | |
| Brussels sprouts | Rosenkohl | |
| Beetroot | R Rüben N, R Rüben R Bio | cooked, the Rewe one is jarred |
| Olives | Oliven A, Oliven A Bio, Oliven R Bio | green, in brine - salt comes from the food anyway |
| Sauerkraut | Sauerkr R, Sauerkr R old | canned / jarred |

### Legumes, nuts, seeds

| Proposed type | Foods | Note |
|---|---|---|
| [ x ] Lentils | Linsen R Bio, Linsen R Bio old | brown, cooked - the food decides dry vs cooked, so pick one and note it |
| Walnuts | Walnuss R Bio | |
| Pistachios | Pistazien | |
| Tofu | Tofu N | plain, firm |

### Dairy

| Proposed type | Foods | Note |
|---|---|---|
| Milk | Milch H, Milch H Fr | whole, 3.5 % |
| Yoghurt | Jogh N gr | natural, 3.8 % |
| Cream cheese | Frischk R Bio 2, Philadelphia, Philadelphia mini | plain double cream, no herbs |
| Cottage cheese | Frischk R Bio | "körniger Frischkäse" |
| Bergkäse | Bergkäse R Bio, Bergkäse R Bio St | Gruyère / alpine hard cheese in the source tables |
| Parmesan | Parmesan Bio R | |
| Tilsiter | Tilsiter R Bio, Tilsiter big, Tilsiter small | |
| Limburger | Limburger | |
| Feta | Patros 150, Patros W | sheep |
| Gouda | Käse Gr Sch (all), Käse Gr W (all) | variety to confirm: 29 g fat / 25 g protein reads like young Gouda |

### Fish

| Proposed type | Foods | Note |
|---|---|---|
| Pollock | Seelachs N | Alaska pollock, raw fillet |
| Tuna | Thunfisch N | canned; confirm water or oil, the two differ a lot |

### Other

| Proposed type | Foods | Note |
|---|---|---|
| Rapeseed oil | Öl | kind to confirm - 6.3 g saturated and 30 mg vitamin E read like rapeseed, not sunflower |
| Sugar | Zucker Stick | white, sucrose |


Borderline - decide before adding
----------------------------------------------------------

| Foods | Question |
|---|---|
| Mais R Bio | canned sweetcorn with added sugar and salt - reference is plain sweetcorn |
| Apfelsaft N, Fruit (grapefruit juice), Saft R Bio | single-fruit juices: pure enough for a reference, but the juice kind of "Saft R Bio" is unknown |
| Adelholzener, Wasser R lg, Wasser lg, Wasser sm | mineral water: nutritionally near zero, only the mineral panel differs, and that is per brand - little to gain |
| Dallmayr classic, Dallmayr deal | coffee: reference tables give brewed coffee, the food is ground powder - amounts would not match |
| Ritter Sp 50/61/74/81 % | dark chocolate is composed, but the tables do carry entries per cocoa percentage |
| Linsen vegan | 61 kcal and 0.95 g salt read like a prepared dish, not plain lentils |
| Apfelmus | apples plus sugar - only the apple part has a reference |


Not in scope
----------------------------------------------------------

The remaining ~136 typeless foods are mixed products: breads, frozen and ready meals,
bars, sweets, chips, nut and salad mixes, spreads, sauces, wraps, pizza, soft drinks.
Their values stay with the food, from packaging or the vendor's page.

Nut mixes (Nussmisch A/N, Nuss Pr N, Durchstarter, Cashew Cranb ...) are mixed by this
rule, but their components are pure - a mix cannot take one `type`.


Housekeeping
----------------------------------------------------------

- `Broccoli_GPT_2505.yml` and `Broccoli_Grok_2505.yml` are not referenced by any food -
  model comparison leftovers next to the used `Broccoli.yml`
- `Poato.yml` is a typo for Potato; renaming it means updating the `type` of the 4 foods
  that use it
- a new default only takes effect once the bundle foods get their `type` - the file alone
  changes nothing

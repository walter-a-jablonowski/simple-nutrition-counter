# AI Voice Agent — Logging Ingredients (Plan)

Second tool set for the voice agent (see `Voice_Agent_Plan.md`): log foods into the day
entries by voice, the spoken equivalent of tapping the amount buttons in `#layout`.

Target utterance, one or many ingredients in a single sentence:

> "200g Gemüse Rewe Bio, 1/2 Dose Linsen, ein Stück Knoblauch, 60ml Olivenöl"


## 1. Verified facts (read from the code before planning)

### The nutrient math is linear — arbitrary amounts are exact, not approximations

`src/models/LayoutView.php:41-88` computes every amount button as

```
weight   = packWeight * multipl        (pack)  |  (packWeight / pieces) * multipl  (pieces)
value    = nutrient * (weight / 100)   (per-100g foods)
value    = nutrient * multipl          (foods with amountPer: piece)
price    = price * (weight / packWeight)
```

Every term is a plain multiplication. The three buttons are just three sampled points on a
straight line — there is nothing special about them, and any other amount is equally valid.
**This decides the amount question in section 3.**

### `usedAmounts` already speaks the user's language

| Shape | Count | Example food | Spoken as |
|---|---|---|---|
| `["25g","50g","100g"]` | ~70 | Gemüse R Bio | "200g" |
| `["1/4","1/3","1/2"]` | ~60 | Linsen R Bio | "1/2 Dose" |
| `[1]`, `[1,2,3]` | ~50 | Knoblauch R (`pieces: 8`) | "ein Stück" |
| `["15ml","30ml","60ml"]` | ~8 | Olivenöl | "60ml" |

`determineFoodUsageType()` (`LayoutView.php:107`) already classifies these as
`precise` / `pieces` / `pack`. The spoken units map 1:1 onto that classification.

### `vendor` is already in the data — no renaming needed

181 of 226 food files carry `vendor:` (Rewe 81, Norma 40, Aldi 28, Denns 9, Edeka 4 …),
68 carry `productName:`. The grid names abbreviate exactly that vendor:
`Gemüse R Bio` = *Gemüse* + **R**ewe + *Bio*. The user speaks the vendor out
(`"Gemüse Rewe Bio"`), so the expansion the agent needs is **already in the yml**.
This decides the identification question in section 3.

### The day entry is shape-agnostic

`#createEntryEl()` (`MainController.js:1310`) writes the whole nutrient tree into
`li.dataset.nutrients` as json and `#syncDayEntriesFromDom()` reads it back. Any nutrient
tree survives the round trip, so a scaled entry needs no format work.

### There are already two entry-building paths, and they are not equal

| Path | Nutrients kept |
|---|---|
| `layoutItemClick()` (`MainController.js:1093`) — grid button | **full**: macros + fat / amino / vit / min / sec / misc |
| `#buildDayEntry()` (`MainController.js:823`) — "consumed now" modal | macros + fibre **only** |

The modal already logs arbitrary amounts, but it drops every micronutrient. Voice logging
must therefore follow the **grid-button** path, not the modal path.

### The grid item carries almost everything needed

`src/view/main/edit/layout/entry.php` renders per button: `data-food`, `data-category`,
`data-amount-label`, `data-amount-weight`, `data-calories`, `data-price`,
`data-nutritionalvalues`, `data-fattyacids`, `data-aminoacids`, `data-vitamins`,
`data-minerals`, `data-secondary`, `data-misc`, `data-x-time-log`.

Missing for voice: the food's **pack weight**, **pieces**, **unit** and **vendor**. All four
are already available in that template (`$weight` is computed at line ~20,
`$this->combinedModel` has the rest) — four more attributes, no new data source.


## 2. Answers to the two open questions

### Amounts: one precise entry, **not** N × a grid button

Log `200g Gemüse` as **one entry of 200g**, not as 2 × 100g.

| | precise single entry | N × button |
|---|---|---|
| Nutrient totals | identical (math is linear) | identical |
| Rounding | rounds **once** | rounds N times |
| "170g" (buttons 25/50/100) | exact | impossible — nearest is 175g |
| Day list | 1 row, reads like what was said | N rows to read and to delete |
| Undo | delete one row | delete N rows |

The deciding point is the third: speech is free-form, so quantising to button multiples
would throw away precision that the data model does not require us to lose. There is no
compensating advantage — the totals are the same either way.

Caveat and its fix: the button values in the dom are already rounded (1 decimal for grams,
5 for mg-scale), so scaling by a large factor amplifies that rounding. Fixed by scaling
from the button **closest to the target** rather than the first one, which keeps the factor
near 1 (see section 5). At factor ≤ 2 the error is ≤ 0.1 g on a macro — far inside the
noise of the pack labels the values come from.

### Identification: expand the vendor, don't rename foods

Give the model the **exact grid vocabulary with the vendor spelled out**, generated from
data that already exists:

```
Gemüse R Bio        (Rewe)          25g | 50g | 100g
Linsen R Bio        (Rewe, Braune Linsen)   1/4 | 1/3 | 1/2   pack 265g
Knoblauch R         (Rewe)          1 piece   pack 80g, 8 pieces
Olivenöl            (Denns)         15ml | 30ml | 60ml
```

With that list in the system instruction, "Gemüse Rewe Bio" → `Gemüse R Bio` is a lookup
the model does reliably, because it sees the closed vocabulary and the vendor expansion.
~274 grid entries ≈ 10 KB ≈ 2.5k tokens — sent once per session, no per-call round trip.

So: **no renaming, and no `voice:` field needed for the main case.**

Keep the `voice:` field as the documented escape hatch for the residue: an optional
`voice: "Gemüse Rewe Bio"` in a food yml is emitted as an extra alias on that food's
vocabulary line. Supported from day one (it costs one attribute), but **populated only
where a food actually misfires** — filling in 226 files up front would be work for a
problem the vendor expansion mostly already solves.

Third layer of safety: the model never free-texts a name into the log. It must pass the
**exact** grid name, and the tool verifies it against the food index. On no match or an
ambiguous match the tool returns candidates and the model asks (same pattern as `findFood`).


## 3. Data flow

```
"200g Gemüse Rewe Bio, 1/2 Dose Linsen"
   |
   v  model maps speech -> exact grid names + structured amounts
toolCall logFoods { items: [ { food: "Gemüse R Bio", value: 200, unit: "g" },
                             { food: "Linsen R Bio", value: 0.5, unit: "pack" } ] }
   |
   v  VoiceAgentController.logFoods()
mainCrl.logFoodAmount( food, value, unit )        per item
   |
   v  find grid item -> target grams -> closest button -> scale the full nutrient tree
#addDayEntry( entry )   ->   dom + summary + save (unchanged path)
   |
   v  toolResponse: per item { result, food, label, weight, calories }
model reads back: "200g Gemüse R Bio and half a can of lentils, 190 kcal together"
```


## 4. Files

### Modified

| File | Change |
|---|---|
| `src/view/main/edit/layout/entry.php` | 4 new data attributes on `.layout-item`: `data-vendor`, `data-food-weight`, `data-food-pieces`, `data-food-unit` (+ optional `data-voice`) |
| `src/MainController.js` | extract `#entryFromButton()`; new public `logFoodAmount()` and `foodVocabulary()`; `#buildFoodIndex()` carries the new fields |
| `src/VoiceAgentController.js` | `logFoods` tool declaration + handler; append the vocabulary to the system instruction |
| `src/data/agent/prompt.md` | logging rules, unit normalisation, confirmation style |
| `dev_info/Voice_Agent_Plan.md` | link to this plan |

### New

None. Everything hangs off existing files.


## 5. `src/MainController.js`

### `#entryFromButton( btn, factor, label )` — extracted, two callers

`layoutItemClick()` today inlines the entry construction (lines 1109-1131). Extract it
unchanged, with a scale factor and an amount label:

- `layoutItemClick()` calls it with `factor = 1` and the button's own label → **byte-identical
  behaviour to today** (the regression check in section 8).
- `logFoodAmount()` calls it with the computed factor and a spoken label.

Scaling is a generic recursive multiply over the nutrient tree (every leaf is a number),
so it does not duplicate any of the server-side nutrient logic — it only re-scales an
already-computed vector. `nutrients.amount` is set, not scaled.

Rounding: macros to 1 decimal, the mg/µg-scale groups to 5, matching `LayoutView.php:82`.

### `logFoodAmount( foodName, value, unit )` — public, the agent's entry point

```
1. rec = findFoods( foodName ), exact name match preferred    // reuses the existing index
   0 matches -> { result: 'none' }
   >1 (same name on several tabs) -> any of them, the food data is identical
2. buttons = rec.itemEl.querySelectorAll('.amount-btn')
3. targetGrams:
     'g' | 'ml' -> value
     'piece'    -> value * (packWeight / pieces)
     'pack'     -> value * packWeight
     'x'        -> value * firstButton.weight     // "twice the usual amount"
     none given -> firstButton.weight             // "log some garlic" = the typical amount
4. ref    = button minimising |targetGrams / btn.weight - 1|
   factor = targetGrams / ref.weight
5. entry  = #entryFromButton( ref, factor, label )
6. #addDayEntry( entry );  #flashItem( the new row )
7. return { result: 'logged', food, label, weight, calories }
```

Guards: unknown unit, `value <= 0`, missing pack weight for `pack`/`piece` foods, and a
sanity ceiling (reject a factor above ~20 as a probable mis-hearing — "2000g olive oil"
should be questioned, not logged).

### `foodVocabulary()` — public, one line per grid food

Deduplicated over the index (a food on two tabs is one vocabulary line), rendering name,
vendor, product name, the button labels, and pack weight / pieces where they exist.


## 6. The tool

```js
{
  name: 'logFoods',
  description: 'Log one or more foods the user ate or is cooking with into today\'s entries. '
    + 'Use the exact food name from the food list in your instructions.',
  parameters: {
    type: 'OBJECT',
    properties: {
      items: {
        type: 'ARRAY',
        description: 'One entry per ingredient the user named',
        items: {
          type: 'OBJECT',
          properties: {
            food:  { type: 'STRING', description: 'Exact food name from the food list' },
            value: { type: 'NUMBER', description: 'Amount, e.g. 200 for "200g", 0.5 for "half a can"' },
            unit:  { type: 'STRING', description: 'g | ml | piece | pack | x' }
          },
          required: ['food']
        }
      }
    },
    required: ['items']
  }
}
```

One call with an array, not one call per ingredient: the user dictates a list in one breath,
so one round trip gives one spoken confirmation and lets a single bad item be reported
without holding up the rest. (The handler already loops over `functionCalls`, so several
parallel calls would also work — the array is about the *response* being one summary.)

Response: `{ result: 'partial'|'ok', items: [ per-item result ] }`, each item either
`logged` with the label, weight and calories, or `none` / `multiple` with candidates so the
model can ask.

### Undo

`undoLastLog` — removes the entries the last `logFoods` call created, via the existing
`deleteEntryBtnClick` path. Small, and the natural repair for a write tool driven by
speech recognition ("no, that was 100 not 200"). The controller remembers the ids it added
in the last call; nothing persists across a page reload.


## 7. `src/data/agent/prompt.md` additions

- The food list (appended at session start, see section 5).
- The name rule: only ever pass a name **from that list**, never invent or translate one.
  If unsure between two, ask before logging — logging writes to the user's day.
- Unit normalisation with examples: "200 Gramm" → `value: 200, unit: 'g'`,
  "eine halbe Dose" → `0.5, 'pack'`, "ein Stück" / "eine Zehe" → `1, 'piece'`,
  "zwei Portionen" → `2, 'x'`.
- Confirm by reading back what came *out* of the tool (label + food + total kcal), never
  what was heard — that is what makes a misheard amount audible.
- Never log the same list twice if the user repeats themselves; ask instead.
- Vendor words ("Rewe", "Aldi", "Bio") are name hints, not separate ingredients.


## 8. Verification

Automated (runnable here):

- `php -l`, `node --check` on the touched files.
- **Regression**: `layoutItemClick()` must produce exactly the entry it produces today.
  With `factor = 1` the extracted `#entryFromButton()` must return a deep-equal object —
  checked against a captured "before" entry.
- **Scaling**: for a food with `["25g","50g","100g"]`, `logFoodAmount(food, 200, 'g')` must
  give the 100g button's tree × 2, and picking the 100g button as reference (not 25g).
- **Live model check** with the probe harness already built for the connection bug: send
  the real declarations + a real vocabulary and the sentence
  *"200g Gemüse Rewe Bio, 1/2 Dose Linsen, ein Stück Knoblauch, 60ml Olivenöl"* as text,
  then assert the emitted `toolCall` args are the four expected `{food, value, unit}`
  triples — in particular that `Gemüse Rewe Bio` came back as `Gemüse R Bio`. This tests
  the identification decision **before** any UI work, and is the cheapest place to find out
  that a food needs a `voice:` alias.

Manual (needs the user):

- Speak one ingredient, check the row, the amount label and the day summary.
- Speak the four-ingredient list, check all four rows and the read-back.
- Speak a deliberately ambiguous food, check that it asks instead of guessing.
- Compare a voice-logged 200g against two taps of the 100g button — the summary totals must
  match.


## 9. Order of work

1. `entry.php` attributes + `#buildFoodIndex()` fields + `foodVocabulary()` — inspectable in
   the console, nothing else depends on it yet.
2. Live model check on identification (section 8) using the vocabulary from step 1. **Do this
   before step 3** — if the vendor expansion does not carry, the `voice:` field moves from
   escape hatch to requirement, and that changes step 3's data work.
3. `#entryFromButton()` extraction + regression check, then `logFoodAmount()`.
4. `logFoods` tool + prompt rules.
5. `undoLastLog`.


## 10. Open points

- **Recipes.** `recipes.yml` is merged into the grid by `CombinedModel`; a recipe logs like
  any other item, so it needs no special handling — but it is untested and the vocabulary
  line has no vendor for it.
- **Which day.** Entries always land on the currently open day. Saying "log that for
  yesterday" is out of scope; the agent should say so rather than log it to today.
- **Supplements** (`category: 'S'`/`'M'`) are grid items too and would work unchanged. Worth
  deciding whether the agent should log them at all, or refuse and let the user tap.
- Later: read back today's totals, and "what's left of my protein target" — the natural
  next tools once writing works.

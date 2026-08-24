# AI Nutrition Advisor (Plan)

A text model reads the nutrient balance of the last days and answers two questions:

> "What am I short of, what did I have too much of — and which of *my* foods fixes that?"
> "Which of those go together into something worth cooking?"

Not the voice agent: this is a reading task with a long answer, so it uses `generateContent`
the way the photo import does (`lib/food_import/PhotoImporter.php`). The voice agent can
trigger it and speak one sentence about it, but the answer lives on screen.


## 1. Verified facts (measured in the data before planning)

### The empty vitamins are not a save bug

Rebuilt the food model and checked every August entry against it:

| | count |
|---|---|
| food has vitamin data, entry stored it | 165 |
| food has vitamin data, entry empty | 20 |
| food has **no** vitamin data, entry empty | 244 |
| food has no data, entry stored some anyway | 0 |

The 244 are foods without a `type`, so they have no panel to store. The 20 are entries
logged **before** that food got its `type` — verified against the commit dates, they turn
filled right after (`Fischst` empty on 08-05, filled from 08-13; type added 08-11).

Recomputing all 94 entries of 2026-08-20..24 from the food model reproduces every stored
value exactly. **The save path is correct.**

### August spans three code versions

| Boundary | Commit | Effect on earlier days |
|---|---|---|
| 2026-08-16 | `925e8feb Added sugar counting` | no `sugar` key, `misc` empty — no water |
| 2026-08-19 | `0a583ef7 Added decimal fix` | gram-unit substances rounded to 1 decimal, so **calcium is 0 in every entry** |

Both already fixed in the repo; the live app was behind. Consequence for this feature:
fully trustworthy days start **2026-08-20**. 08-16..08-19 are usable without calcium,
earlier days without water. A 30-day window reaches into all three versions, so the report
must not present old days as equal to new ones — see the coverage rule in section 3.

### Fatty acids never reach a day entry — **fixed, step 0 done**

Measured over August: fat group `has-data-and-stored = 0`, `has-data-but-empty = 161`.

`AppController::NUTRIENT_GROUPS` holds `'lipids/fattyAcids'`, which is the **path** to
`nutrients/lipids/fattyAcids.yml`. `CombinedModel` and `LayoutView` then use that same
string as the **key inside the food record**. But all 31 `food_defaults/*.yml` and
`_blank_food.yml` write `fattyAcids:`, so the lookup finds nothing and emits `fat: {}`.

The only food that ever logs fatty acids is `supplements/Algen.yml`, which happens to use
the literal key `"lipids/fattyAcids"` — that is the 13 non-zero entries.

The intended convention is already written down elsewhere in the repo,
`tools/data_verification/verify_food_defaults.php:158`:

```php
// yml group => substance file in /nutrients
const GROUPS = ['fattyAcids' => 'lipids/fattyAcids', ...];
```

The substance names already match (`Alpha-linolenic acid` → `ALA`), so nothing but the key
is in the way. `view/main/edit/layout/food_info/content.php` has the same mistake, which is
why the food info popover shows no fatty acids either.

This had to be fixed before the advisor ships. Omega-3 is exactly what such a feature would
flag, and it would have been reporting a code bug as a dietary deficit.

Fixed by `group_food_key()` in `models/functions.php` (`basename()` of the group name),
used in `CombinedModel`, `LayoutView` and the food info popover; `Algen.yml` now uses the
plain key like every other food file. Measured before and after, first amount of every
food: `fat` 1 → **80** foods, every other group unchanged (`nutriVal` 253, `amino` 79,
`vit` 90, `min` 91, `misc` 100, `sec` 3).

### Typed foods are a third of the entries but three quarters of the calories

| | share |
|---|---|
| foods with a `type` | 79 / 253 (31 %) |
| logged **entries** from typed foods | 33 % |
| logged **calories** from typed foods | **78 %** |

The untyped two thirds are coffee, water, supplements and small items. Restricting the
micronutrient analysis to foods that carry data costs 22 % of the calories, not 67 %.

### Macro data is complete, micro data is not

| nutrient | foods carrying it |
|---|---|
| calories, fat, carbs, sugar, amino, salt | 100 % |
| saturatedFat | 99 % |
| fibre | 44 % |
| vitamins / minerals (via `type`) | 31 % |

This asymmetry decides the shape of the feature (section 2).

### The targets and the aggregation already exist

- `models/NutrientsView.php` computes `lower` / `ideal` / `upper` per nutrient from
  `nutrients/*.yml`, and `widgetRanges` on group level.
- `ajax/get_range_nutrients.php` aggregates any range into daily averages, and
  `rangeDates()` already excludes the running day.
- `bundles/…/-this.yml` holds the diet rules (`framework`, `conceptMisc`, `goals`,
  `primaryGoals`) — the user avoids milk products, bread, cereals, sugar, alcohol and
  limits fruit.
- `GeminiVisionClient` is documented as transport-only and works with an empty image list
  (now `lib/ai/GeminiClient.php`, see step 3).


## 2. Decisions

### No `type` flag in the day lines

Not needed. An entry counts as measured for a nutrient group exactly when its own blob
carries a non-empty value for that group — which is what the data already says, and it
works retroactively across all 740 day files. A flag would only record the state at
logging time, and the blob already is that record.

### Deficits are restricted to measured entries, excesses are not

Two analyses with different reach, because the data has different reach:

| | source | coverage |
|---|---|---|
| deficits, recommendations | entries whose blob carries the group | ~78 % of calories |
| excesses, avoid list | **all** entries | ~100 % |

Salt, sugar, saturated fat and calories are printed on every pack, so the "too much" side
needs no caveat. Only the "too little" side is data-limited. Restricting both would throw
away the half of the feature that already works.

### Raw sums plus coverage, never extrapolation

The report sends the measured sum **and** the share of calories it was measured from. It
must not scale the sum up by `1 / coverage`: the uncovered foods are coffee and water,
which genuinely contain nothing, so extrapolating would invent nutrients. The model is
told to distinguish "you had too little" from "we cannot tell", and to name the data gap
as a finding — which turns the weakness into the feature that tells the user which foods
to give a `type` next.

### Two ranges, not one

7-day and 30-day averages side by side. Micronutrients need weeks; salt and calories are a
daily matter. One range cannot serve both, the aggregation already exists, and running it
twice costs nothing. The nutrients tab dropdown stays the manual override.

### Two model calls, not one

| call | input | output | why separate |
|---|---|---|---|
| `analyse` | report + candidates + rules | assessment, deficits, excesses, recommended, avoid | near-deterministic, worth caching |
| `menus` | recommended foods + vocabulary + rules | menus | creative, the user will re-roll it |

The user may take single ingredients and never ask for menus. One large schema is also
harder to constrain and slower to first output.

### Menus are invented, not read

`bundles/…/recipes.yml` exists but is unimplemented. The advisor does **not** read it and
does **not** write to it. The model combines the recommended ingredients itself and may add
a few from the food list for taste, under one hard rule: an added ingredient must come from
the grid and must not push a nutrient that is already above `upper` further up. Each
ingredient is tagged `core` (from the recommendation) or `taste` (added), so the rule stays
auditable in the ui.

### Its own modal, not the agent overlay

`AgentOverlayController` is deliberately an aid to a spoken question — "only an aid for
lists too long to hold in your head". The advice is a document with sections, a re-roll
button and per-row actions, and it must also work with `agent.enabled: false`. So it gets
`#advisorModal`. The agent overlay is left alone, which also means the auto-hide list in
`handleToolCall()` needs no change.


## 3. The report (deterministic, no model involved)

`lib/advisor/NutrientReport.php` produces one structure per range:

```
range:        7 | 30
daysInRange:  int
daysWithData: int

nutrients:
  - group:    vitamins
    nutrient: Vitamin D
    short:    D
    unit:     mg
    perDay:   0.004        # measured daily average
    lower:    0.018
    ideal:    0.02
    upper:    0.05
    status:   below | ok | above
    gap:      0.016        # ideal - perDay, only when below
    over:     null         # perDay - upper, only when above
    coverage: 78           # % of calories the value was measured from
    measurable: true       # coverage >= minCoverage

macros: { calories, fat, carbs, amino, salt, price, fibre, sugar, water }
```

Coverage per group, over the same dates:

```
coverage[group] = calories of entries carrying blob[group] / all calories
```

"Carrying" is a non-empty array for a real group, and the key itself for fibre and
sugar — an entry written before those existed has no key at all, while `fibre: 0` is the
food's answer and counts.

**Fibre and sugar carry their own coverage**, not the carbs group's. They were added to
the entry at different times, so a pre-2026-08-16 entry has fibre but no sugar and only
sugar's number may drop for it. Keys: `carbs.fibre`, `carbs.sugar`.

No `eatingTime` in the macros: it is a widget value, and no food the advisor could
recommend would change it.

Below `advisor.minCoverage` (default 40) a group is marked `unmeasurable` and its
nutrients are excluded from the deficit list and from ranking — they go into `dataNotes`
instead, with the foods that would close the gap if they had a `type`.

`status` uses the same bounds the nutrients tab prints, so the panel and the tab can never
disagree.

### What the first real run showed (anchor 2026-08-25)

| | 7 days | 30 days |
|---|---|---|
| days with data | 6 / 7 | 27 / 30 |
| coverage vit / min / amino | 74 % | 58 % |
| coverage `carbs.sugar` | 100 % | **33 %** |
| coverage `fat` | **1 %** | **0 %** |
| measurable below / above | 16 / 20 | 26 / 5 |
| unmeasurable | 7 | 8 |

Two of these are the design working as intended:

- `carbs.sugar` at 33 % over 30 days is the 2026-08-16 boundary, found by the coverage
  rule on its own, with no date logic anywhere.
- `fat` near zero because the step 0 fix only reaches entries logged **after** it. The
  stored history keeps `fat: {}`, so fatty acids stay unmeasurable until enough new days
  accumulate — which is the honest answer, not a deficit.

**Water is the one case coverage does not catch.** It reads 223 g/day against a 3000 g
target, and `misc` coverage is 75 %, so nothing looks wrong. The cause is logging
behaviour, not food data: the user does not log what they drink, and coffee is logged as
2.5 g of powder, which contributes 2.5 g of water. Coverage measures missing *food data*,
never un-logged intake. So water must stay out of the deficit list and out of the ranking
in step 2 — "drink more" is not a food recommendation.


## 4. Candidate ranking (deterministic)

`lib/advisor/FoodRanking.php`. Input: the deficit vector and `layoutView`. Only foods with
a `type`, since only those carry the panel.

For each food at its middle `usedAmounts` entry:

```
gain    = Σ nutrients below:  min( value, gap ) / gap        # capped at the gap
penalty = Σ nutrients above:  (value/upper) * (over/upper)   # weighted by the overshoot
net     = gain - penalty
score   = net / max(kcal, 100) * 100 / (1 + 0.15 * eaten)
```

Four things the first real run forced, all of them visible in the output before the fix:

- **Per 100 kcal, not per portion.** The list was `Linseneintopf R` 552 kcal, `Avocado gr`
  416, `Currywurst` 345 — the score was rewarding portion size. The day holds a fixed
  number of calories, so nutrient density is the question. Afterwards the top is
  `Hähnchenbr Spr` at 105 kcal and `Pfannegem A Ital` at 38.
- **Penalty weighted by the overshoot.** The amino acids sit 6 % over, so every protein
  food was penalised as hard as one feeding a nutrient at five times its bound.
- **`state: unprecise` is deliberately *not* scored down.** A kebab typed as `Chicken`
  inherits chicken's whole vitamin table, so the first version halved it. Reverted on the
  user's decision: rough numbers are the numbers the app has, data quality only rises over
  time, and some error in the results for a while is acceptable. A permanent handicap would
  keep a food out of the list long after its panel was fixed. The flag still travels with
  the candidate, so the model can mention it.
- **Repeat is a divisor, not a subtraction.** Once the score is a density, subtracting a
  fixed amount per logging is on the wrong scale.

The shortlist deliberately does **not** apply the user's diet rules — that is the model's
job in step 4. It is why processed meats still appear here: the maths likes them, the
bundle's own rules do not, and the two are separate concerns.

The top `advisor.maxFoods` (default 40) go to the model, carrying **only** the nutrients
that appear in the deficit or excess list — not the whole panel — plus `usedAmounts`,
`vendor`, and the flags the food files already hold: `acceptable`, `careful`, `noUseIf`,
`limit`.

Ranking deterministically first keeps the prompt small, makes the choice testable without
a model call, and makes it impossible for the model to name a food that is not in the grid.

The excess side is built from all entries with the contribution logic the nutrients tab
already uses (`MainController.#renderNutrientRows`, the per-nutrient food table): for each
nutrient above `upper`, the foods it came from, largest first.


## 5. Data flow

```
nav button / voice tool
  -> ajax getAdvice { step: 'analyse', date, ranges: [7, 30] }
       NutrientReport   ->  report per range        (deterministic)
       FoodRanking      ->  top 40 candidates       (deterministic)
       NutritionAdvisor ->  prompt + schema -> GeminiClient -> mapped answer
       cache            ->  data/users/<id>/advice/<date>.yml
  -> AdvisorController renders #advisorModal
  -> "Make menus" -> ajax getAdvice { step: 'menus' }  (reads the cached analysis)
```

Cache key: date plus a hash of the report. Re-opening the panel on an unchanged day is
instant and free; logging another food invalidates it. The menu step reads the cached
analysis, so it never re-runs the first call.


## 6. Files

### Modified

| File | Change |
|---|---|
| `src/models/functions.php` | step 0 — `group_food_key()` ✔ |
| `src/models/CombinedModel.php` | step 0 — read and write the food-record key, not the model path ✔ |
| `src/models/LayoutView.php` | step 0 — same, in the nutrient loop ✔ |
| `src/view/main/edit/layout/food_info/content.php` | step 0 — same, plus the field list in its doc block ✔ |
| `src/data/bundles/…/supplements/Algen.yml` | step 0 — `"lipids/fattyAcids":` → `fattyAcids:` ✔ |
| `src/lib/helper.php` | step 1 — `DAY_FILE_HEADERS` + `read_day_file()` ✔ |
| `src/models/functions.php` | step 1 — `range_dates()`, moved out of the ajax trait ✔ |
| `src/ajax/get_range_nutrients.php` | step 1 — uses both, its private `rangeDates()` is gone ✔ |
| `src/tools/test_range_nutrients.php` | step 1 — follows the move ✔ |
| `src/AppController.php` | step 1 — `DAY_HEADERS = DAY_FILE_HEADERS` ✔ ; later `use GetAdviceAjaxController` |
| `src/lib/food_import/PhotoImporter.php` | require path of the moved client |
| `src/tools/test_photo_import.php` | same |
| `src/config.yml` | `advisor:` block |
| `src/view/-this.php` | nav entry (sidebar + mobile, right of the mic), modal include, script, instantiation |
| `src/VoiceAgentController.js` | `analyseNutrition` declaration + handler |
| `src/data/agent/prompt.md` | the tool's wording |
| `dev/AI/tools.md` | new tool row |

### New

| File | Holds |
|---|---|
| `src/tools/test_food_defaults_merge.php` | step 0 — the food defaults merge, end to end ✔ |
| `src/lib/ai/GeminiClient.php` | step 3 — moved from `lib/food_import/GeminiVisionClient.php`, `temperature` and `maxOutputTokens` are `$options` now ✔ |
| `src/lib/advisor/NutrientReport.php` | step 1 — intake vs targets per range, coverage per group ✔ |
| `src/lib/advisor/FoodRanking.php` | step 2 — deficit vector -> scored candidates ✔ |
| `src/lib/advisor/NutritionAdvisor.php` | prompts, schemas, answer mapping |
| `src/ajax/get_advice.php` | both steps, plus the cache |
| `src/data/advisor/analysis_prompt.md` | system instruction, call 1 |
| `src/data/advisor/menu_prompt.md` | system instruction, call 2 |
| `src/AdvisorController.js` | panel, both steps, the row actions |
| `src/view/modal/advisor.php` | the panel markup |
| `src/style/advisor.css` | its styles, next to `agent.css` |
| `src/tools/test_nutrient_report.php` | step 1 — offline, the report maths against fixture day files ✔ |
| `src/tools/test_advisor.php` | offline, replays a recorded answer through the mapping |

`GeminiVisionClient` moves because a second feature now uses it and it was never about
food or about images. Its name is the only thing that said otherwise.


## 7. The two schemas

### Call 1 — analyse

```
summary:     string            # 2-3 sentences, plain language
dataNotes:   [string]          # what could not be measured, which foods need a type
deficits:    [{ nutrient, comment }]
excesses:    [{ nutrient, comment, fromFoods: [string] }]
recommended: [{ food, amount, because: [nutrient], comment }]
avoid:       [{ food, reason }]
```

### Call 2 — menus

```
menus: [{ title,
          ingredients: [{ food, amount, role: core | taste }],
          why,
          instructions }]
```

`food` must be an exact grid name in both, the same rule `logFoods` already lives by.


## 8. Prompt content

Both prompts get the diet rules from `bundles/…/-this.yml` (`framework`, `conceptMisc`,
`goals`, `primaryGoals`), stripped of their html. This is the highest-value context and the
easiest to forget: without it the model answers low calcium with milk, which this user does
not eat.

Fixed rules in the system instruction:

- Never diagnose, never name a condition. Say "this food would raise X", never "you are
  deficient in X".
- Only foods from the list given. Never invent one, never translate a name.
- A nutrient marked `unmeasurable` is a data gap, not a deficit — say so and name the foods
  that need a `type`.
- The avoid list names where an over-limit nutrient came from. It does not tell the user to
  stop eating something.
- Answer in the language of the app's data (German food names stay as they are).

The panel carries a line from `misc/disclaimer.php`.


## 9. Ui

`#advisorModal`, sections in this order: summary, what is short, what is too much, foods to
use today, foods behind the excess, then the menus once asked for.

| Element | Action |
|---|---|
| recommended food row | "Show" → `mainCrl.jumpToFood()` |
| recommended food row | amount button → `mainCrl.logFoods()` |
| "Make menus" | call 2, appends the menu cards |
| menu card | "Log this" → `mainCrl.logFoods()` with every ingredient |
| menu card | `taste` ingredients marked, so the added ones are visible |

Every action goes through `MainController`, so the panel, the grid and the voice path do
the same thing — the rule `dev/AI/tools.md` already states for the agent tools.

Nav: sidebar above the mic; mobile bottom nav **right of the mic**, no sub menu. Gated on
`advisor.enabled` alone, so it works with the voice agent switched off.


## 10. The voice tool

`analyseNutrition`, no arguments.

It must answer at once — a pending toolCall keeps the model silent, and this call takes
20-40 s. So it returns `{ result: 'running' }` immediately, exactly the pattern
`showChoices` established, and the result arrives later as a user turn carrying the
`summary`. The agent then says one sentence and points at the screen; it never reads the
list out. If no session is running the panel simply opens.


## 11. Config

```yaml
advisor:                    # AI nutrition advisor, see dev_info/Nutrition_Advisor_Plan.md

  enabled:     true         # false hides the nav entry and the voice tool
  model:       "gemini-3.6-pro"   # reasoning task, runs rarely - flash is the wrong trade here
  ranges:      [7, 30]      # days, both are analysed
  maxFoods:    40           # candidates sent to the model
  minCoverage: 40           # % of calories a group must be measured from to count
  debug:       false        # log the report and the raw answer
```


## 12. Verification

```
cd src
php tools/test_food_defaults_merge.php   # step 0, offline
php tools/test_nutrient_report.php       # step 1, offline, no network, no cost
php tools/test_range_nutrients.php       # must still pass, range_dates() moved
php tools/test_food_ranking.php          # step 2, offline
php tools/test_advisor.php               # offline, replays a recorded answer
php tools/test_layout_view.php           # must still pass
php tools/test_widget_ranges.php         # same
```

`test_food_defaults_merge.php` walks the real bundle and checks that every nutrient group
of a food default arrives in the food model and on the amount buttons. Data driven, so a
default gained or lost is no reason for a red test. Written before the fix, where it
failed for all 79 typed foods.

Manual, after step 0: log a `Brokkoli R` and check the day line carries
`fat: {ALA: …, LA: …}` instead of `fat: {}`.

Note: `tools/test_layout_functions.php` has one unrelated pre-existing failure
(`prepend puts item on top`), the same before and after step 0.


## 13. Order of work

| Step | Work |
|---|---|
| 0 | **done** — fatty acid key fix, failing test first. Independent of everything below |
| 1 | **done** — `NutrientReport` + `tools/test_nutrient_report.php`. Useful on its own — it says whether the analysis can be trusted at all |
| 2 | **done** — `FoodRanking`, tested against a hand made deficit vector |
| 3 | **done** — moved to `lib/ai/GeminiClient.php`, `extract()` → `ask()` with an `$options` array, require paths fixed |
| 4 | `NutritionAdvisor` call 1 + `ajax/get_advice.php` + cache + `tools/test_advisor.php` |
| 5 | `#advisorModal`, `AdvisorController`, nav entries |
| 6 | Call 2, the menus, and the menu cards |
| 7 | Voice tool + prompt + `dev/AI/tools.md` |

Steps 1 and 2 produce no model call and no cost, and they are where the feature is either
honest or not. Nothing after step 4 changes what the numbers say.


## 14. Open points

- **Recompute history from the current food model?** It would make the 740 stored days
  consistent and pick up every food-data improvement retroactively, including the calcium
  that 08-16..08-19 lost. Feasible — the day line holds `food` and `amount.weight`, and
  `CombinedModel` has the per-100 g values. Left out here on purpose: a logged day is a
  record, and silently rewriting it is surprising. Belongs behind an explicit "recompute"
  action if it is wanted, and it would help the nutrients tab as much as this feature.
- **Weighting in `gain`.** Currently every deficit nutrient counts the same. Weighting by
  severity is a one-line change once there is a real report to look at.
- **`fibre` at 44 % coverage** sits between the two halves. Treated as a micronutrient
  (coverage rule applies) rather than as a macro.

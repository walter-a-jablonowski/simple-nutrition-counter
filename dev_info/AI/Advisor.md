# AI Nutrition Advisor

Quick reference. Full reasoning: [Nutrition_Advisor_Plan.md](../Nutrition_Advisor_Plan.md)


## What it does

- Reads the nutrient balance of the last days, says what is short and what is too much
- Suggests foods from the user's own grid that close the gap
- On demand: combines those into menus (invented, not read from `recipes.yml`)
- Text model (`generateContent`), not the voice agent — the agent only triggers it


## Data reach — the two halves differ

| | source | coverage |
|---|---|---|
| deficits, recommendations | entries whose blob carries the group | ~78 % of calories |
| excesses, avoid list | **all** entries | ~100 % |

- Macros (calories, fat, carbs, sugar, amino, salt) are on every pack → 100 % of foods
- Vitamins / minerals need a `type` → only 79 of 253 foods (31 %)
- But those 79 are **78 % of the calories** — the untyped rest is coffee, water, small items
- So: restrict the deficit side, never the excess side
- `fibre` (44 %) counts as a micronutrient, not a macro


## Coverage — the number that keeps it honest

- Per group: `calories of entries carrying blob[group] / all calories`
- "Carrying" = non-empty array for a real group, key present for fibre / sugar
- `fibre: 0` is the food's answer and counts; a missing key is "not measured"
- **Fibre and sugar have their own coverage** (`carbs.fibre`, `carbs.sugar`) — they were added at different times
- Below `minCoverage` → `measurable: false`, value still reported, never read as a deficit
- Catches the format boundaries by itself: `carbs.sugar` reads 33 % over 30 days, no date logic anywhere

**What coverage does not catch: un-logged intake.** Water reads 223 g/day against 3000 with
`misc` coverage at 75 % — the user just does not log what they drink, and coffee is logged
as 2.5 g of powder. Coverage measures missing *food data*, never missing entries. Keep
water out of the deficit list and out of the ranking.

**Salt is read from the tsv column, not the mineral group.** It exists twice: the column
every food fills from its packaging, and `minerals.Salt`, which only typed foods carry.
Read from the group it looks like half the real intake — a false deficit at 58 % coverage
against a macro that is on target.

**Fatty acids stay unmeasurable for now.** The step 0 fix only reaches entries logged after
it; stored history keeps `fat: {}`, so coverage is 0–1 % until new days accumulate.


## Data quality — trustworthy days start 2026-08-20

| From | State |
|---|---|
| before 2026-08-16 | no `sugar` key, `misc` empty → no water |
| 2026-08-16 … 08-18 | gram units rounded to 1 decimal → **calcium is 0** |
| **from 2026-08-20** | complete |

- Caused by two fixes landing mid-month (`925e8feb` sugar, `0a583ef7` decimals), live app was behind
- A 30-day window spans all three formats — the report must carry this, not just the `type` gap
- Empty vitamins before a food got its `type` are **not a bug** — the blob is a snapshot at logging time
- Save path verified: 94 entries of 08-20..24 recompute from the food model exactly


## Core rules

- **Raw sums + coverage, never extrapolate.** Uncovered foods are coffee and water — scaling by `1/coverage` would invent nutrients
- **Two ranges**, 7 and 30 days. Micronutrients need weeks, salt and calories are daily
- **Two model calls.** `analyse` (cacheable) then `menus` (re-rollable)
- **No `type` flag in the day lines.** A group counts as measured when its blob is non-empty — works retroactively over all 740 day files
- **Deterministic first.** Report and ranking in PHP, only the wording and the combining go to the model
- Candidates: top 40 by score, carrying only the nutrients in play
- Menu additions for taste must come from the grid and must not raise a nutrient already above `upper`; tagged `core` / `taste`
- Diet rules from `bundles/…/-this.yml` go into both prompts — without them the model answers low calcium with milk
- Never diagnose. "This food would raise X", never "you are deficient in X"


## Ranking (`FoodRanking`)

```
gain    = Σ nutrients below:  min( value, gap ) / gap      # capped at the gap
penalty = Σ nutrients above:  (value/upper) * (over/upper) # weighted by how far over
net     = gain - penalty
score   = net / max(kcal,100) * 100 / (1 + 0.15*eaten)
```

- **Per 100 kcal, not per portion** — otherwise the heaviest dish always wins
- **Gain capped at the gap** — a food huge in one nutrient loses to one covering three
- **Penalty weighted by overshoot** — 1 % over is not 500 % over
- **`state: unprecise` is not scored down** — its numbers are rough but they are the numbers the app has, and the data gets better as it is filled in. A permanent handicap would keep foods out long after their panel was fixed. The flag travels with the candidate so the model can say so
- Candidates: typed foods only, must close at least one gap, top `maxFoods`
- Contributors (which foods caused an excess): **all** foods, no type needed
- Excesses need no `measurable` check — missing data can only under-report, never over-report
- Diet rules are **not** applied here; the shortlist is nutrient maths, the model judges fit


## Ui

- Own `#advisorModal` — not the agent overlay, which is an aid to a spoken question
- Nav: sidebar above the mic, mobile bottom nav **right of the mic**, no sub menu
- Gated on `advisor.enabled` alone, works with the voice agent off
- Row actions go through `MainController` (`jumpToFood`, `logFoods`) — same path as the grid and the agent
- Voice tool `analyseNutrition` returns `{ result: 'running' }` at once, result arrives later as a user turn


## Build status

| Step | State |
|---|---|
| 0 fatty acid key fix | done — `tools/test_food_defaults_merge.php` |
| 1 `NutrientReport` | done — `tools/test_nutrient_report.php` |
| 2 `FoodRanking` | done — `tools/test_food_ranking.php` |
| 3 `GeminiClient` move | done — `lib/ai/GeminiClient.php` |
| 4 analyse call + ajax + cache | next — first step that costs money |
| 5 modal + controller + nav | |
| 6 menus | |
| 7 voice tool | |

Step 1 also moved two shared things out of the way: `read_day_file()` +
`DAY_FILE_HEADERS` into `lib/helper.php`, and `range_dates()` out of the
`getRangeNutrients` trait into `models/functions.php` — so the tab and the advisor
mean the same thing by "7 days".


## Fatty acids bug (fixed, step 0)

- `NUTRIENT_GROUPS` holds `lipids/fattyAcids` — a **path** under `/nutrients`
- `CombinedModel` and `LayoutView` used it as the **key in the food record**, where all 31 defaults write `fattyAcids`
- Result: every food default's fatty acids were dropped, `fat: {}` in 161 of 174 August entries
- Fix: `group_food_key()` in `models/functions.php` (`basename()` of the group name)
- Effect: `fat` on the amount buttons 1 → 80 foods, every other group unchanged
- Guarded by `tools/test_food_defaults_merge.php`


## Config

```yaml
advisor:
  enabled:     true
  model:       "gemini-3.6-pro"   # reasoning task, runs rarely
  ranges:      [7, 30]
  maxFoods:    40
  minCoverage: 40                 # % of calories a group needs to count
  debug:       false
```


## Open

- Recompute history from the current food model? Would fix the lost calcium and pick up food-data improvements retroactively. Left out: a logged day is a record
- `gain` weights every deficit nutrient equally
- Every candidate "raises" the amino acids that sit 1 % over — the numbers are right and the penalty is tiny, but the list reads noisy

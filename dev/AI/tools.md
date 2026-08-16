# Voice agent - tools

The tools the AI voice agent (Gemini Live) can currently call.

Declared in `src/VoiceAgentController.js`, `toolDeclarations()`. They go into the setup
message at session start and cannot be added later. Every call is answered in
`handleToolCall()` - the Live api has no automatic tool handling, an unanswered call makes
the model wait forever.

The wording the model gets for each tool is in `src/data/agent/prompt.md` (system
instruction), the food list it may name is `MainController.foodVocabulary()`.


Tools overview
----------------------------------------------------------

Finding and logging

| Tool | Arguments | Does | Returns |
|---|---|---|---|
| `findFood` | `name`, `tab` (opt.) | Jumps to a food in the grid (activates the tab, scrolls, flashes it) | `jumped` + place, `multiple` + all places, `none` |
| `openFoodSearch` | `query` | Opens the search overlay prefilled, user picks by hand | `opened` |
| `logFoods` | `items[]` of `{ food, value, unit }` | Writes a whole spoken list into the open day | `ok` / `partial` + one result per item |
| `showChoices` | `foods[]`, `title` (opt.) | Puts candidate foods on screen while the agent asks out loud | `shown` (the tap arrives later as a user turn) |

Correcting the day

| Tool | Arguments | Does | Returns |
|---|---|---|---|
| `listDayEntries` | - | Reads the open day, and hands out the ids the other two take | `count` + `[{ id, food, amount, calories, time }]` |
| `updateEntry` | `id`, `value`, `unit` | Rescales one entry to a new amount, in place | `updated` + `from`, `label`, `calories`, or `gone` |
| `removeEntry` | `id` | Deletes one entry, wherever it sits | `removed` + food and amount, or `gone` |
| `undoLastLog` | - | Takes back everything the last `logFoods` call added | what was removed, or `nothing` |


Implementation
----------------------------------------------------------

The controller only marshals arguments and results; the work sits in `MainController`, so
the voice path and the ui path do the same thing.

```
findFood        -> mainCrl.findFoods() + mainCrl.jumpToFood()
openFoodSearch  -> mainCrl.openSearch( null, query )
logFoods        -> mainCrl.logFoods()
showChoices     -> agentOverlay.choose()          # AgentOverlayController.js
listDayEntries  -> mainCrl.listDayEntries()
updateEntry     -> mainCrl.updateEntry()
removeEntry     -> mainCrl.removeEntry()
undoLastLog     -> mainCrl.undoLastLog()
```

Notes

- Only plain values go back to the model, never the dom nodes the food index carries.
- `showChoices` answers at once and does not wait for the tap - a pending toolCall keeps
  the model silent, so waiting would freeze the conversation.
- `logFoods` and `updateEntry` share `buildEntry()`, so a correction rescales exactly the
  way the first logging did.
- Entry ids (`e1`, `e2`, …) live on `li.dataset.uid` for as long as the page does. Rows
  php rendered at load get one the first time `listDayEntries` runs. They are not synced
  into `dayEntries`, so nothing of them reaches the stored day.
- `undoLastLog` cannot reach past the last `logFoods` call. Anything else the user names
  is `listDayEntries` + `updateEntry` / `removeEntry` - correcting by logging again would
  leave the wrong entry in place and count the food twice.


Tests
----------------------------------------------------------

```
cd src
node tools/test_voice_entries.mjs
```

Standalone, no dependencies: a fake dom under `MainController`, loaded through
`new Function`. Covers the entry tools, including the case they exist for - a correction
to the first of several ingredients logged across more than one `logFoods` call.

What it cannot cover is whether the model *chooses* the right tool; that is prompt
behaviour and needs a real voice session.

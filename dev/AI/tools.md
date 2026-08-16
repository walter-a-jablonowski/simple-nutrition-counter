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

| Tool | Arguments | Does | Returns |
|---|---|---|---|
| `findFood` | `name`, `tab` (opt.) | Jumps to a food in the grid (activates the tab, scrolls, flashes it) | `jumped` + place, `multiple` + all places, `none` |
| `openFoodSearch` | `query` | Opens the search overlay prefilled, user picks by hand | `opened` |
| `logFoods` | `items[]` of `{ food, value, unit }` | Writes a whole spoken list into the open day | `ok` / `partial` + one result per item |
| `showChoices` | `foods[]`, `title` (opt.) | Puts candidate foods on screen while the agent asks out loud | `shown` (the tap arrives later as a user turn) |
| `undoLastLog` | - | Takes back what the last `logFoods` call added | what was removed, or `nothing` |


Implementation
----------------------------------------------------------

The controller only marshals arguments and results; the work sits in `MainController`, so
the voice path and the ui path do the same thing.

```
findFood        -> mainCrl.findFoods() + mainCrl.jumpToFood()
openFoodSearch  -> mainCrl.openSearch( null, query )
logFoods        -> mainCrl.logFoods()
showChoices     -> agentOverlay.choose()          # AgentOverlayController.js
undoLastLog     -> mainCrl.undoLastLog()
```

Notes

- Only plain values go back to the model, never the dom nodes the food index carries.
- `showChoices` answers at once and does not wait for the tap - a pending toolCall keeps
  the model silent, so waiting would freeze the conversation.
- `undoLastLog` only ever undoes the last batch; older entries the user deletes by hand.

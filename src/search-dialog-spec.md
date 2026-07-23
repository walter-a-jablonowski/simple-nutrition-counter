# Search Dialog — Functional Specification

A portable description of an in-app **search overlay** (activated with **Ctrl/Cmd+K**).
Hand this file to an LLM to rebuild the same dialog in another app. The **behavior**
sections must be reproduced as-is; the **Data adapter** section is the only part that
changes per app, because each app stores its items differently.

---

## 1. Purpose

A fast, keyboard-driven overlay that searches **all items already loaded in the
browser** and lets the user jump straight to any match. It is a lookup tool, not an
editor: selecting a result opens that item in the app's existing detail/edit view.

Key principle: **search is a plain in-memory walk, no server request.** The full
(non-archived) dataset is assumed to be present client-side. If your app loads data
lazily, load or index it before opening the dialog.

---

## 2. Activation & dismissal

| Trigger | Action |
| --- | --- |
| `Ctrl+K` / `Cmd+K` (not with Alt) | Open the dialog. Prevent the browser default. |
| A menu button / command entry | Open the dialog. |
| `Esc` while open | Close the dialog. |
| Click on the dark backdrop (outside the panel) | Close the dialog. |
| Close (`×`) button in the header | Close the dialog. |

On open: clear the previous query and results, show the panel, and **focus the input**
(after the panel is visible, e.g. on the next tick). If a global menu/dropdown is open,
close it first.

Opening and closing is done purely by toggling a CSS `show` state on the modal — no
DOM rebuild of the shell.

---

## 3. Layout

A centered modal over a semi-transparent backdrop, with two stacked regions:

```
┌────────────────────────────────────────────┐
│  Search                                 ×   │   header
├────────────────────────────────────────────┤
│  [ Search all items… (press Enter)   ] [🔍] │   input bar (sticky)
├────────────────────────────────────────────┤
│  Result name            Location › Section  │
│  … snippet with the match highlighted …     │   results list (scrolls)
│  ------------------------------------------ │
│  Another result         Location            │
│  …                                          │
└────────────────────────────────────────────┘
```

- **Header**: title ("Search") + close button.
- **Input bar**: a text input with placeholder, plus a search-trigger button (magnifier).
  Sticky at the top; does not scroll with results.
- **Results list**: scrollable; each result is a full-width button.
- The panel has a max width (~560px) and a capped height; only the results list scrolls.
- Must be usable on mobile (full-width/near-full-screen panel, tap targets ≥ ~40px tall).
- Theme-aware if the app has light/dark themes.

---

## 4. Search behavior

### 4.1 When search runs
- Pressing **Enter** in the input runs the search.
- Clicking the magnifier button runs the search.
- (Live-as-you-type is intentionally NOT used in the reference; keep it explicit unless
  you deliberately choose otherwise.)
- An empty/whitespace-only query clears the results and does nothing else.

### 4.2 Matching rules
- Case-insensitive substring match (lowercase the query, lowercase the field).
- Match against, in this priority order, and stop at the first field that matches:
  1. the item's **description/body text**,
  2. the item's **tags/labels**,
  3. the item's **name/title**.
- The snippet shown comes from whichever field matched first.
- **Nested items** (e.g. subtasks / children) are also searched, and each matching child
  is listed as its own result, labeled with its parent.
- Ignore internal/meta fields (e.g. keys prefixed with `_`) and any storage-only markers
  that are invisible in the UI (see 4.4).

### 4.3 Traversal
Walk the whole in-memory data tree recursively:
- Skip meta keys and non-object values.
- At each node decide **"is this an item or a container?"** via the data adapter
  (section 6). If it's an item, test it for a match (and test its nested children). If
  it's a container, recurse into it.
- Collect matches into a flat list, preserving traversal order.

### 4.4 Snippet extraction
- Find the match position in the field, take a window of ~30 characters of context on
  each side, collapse runs of whitespace to single spaces, and trim.
- Prefix with `… ` if the window starts mid-text; suffix ` …` if it ends mid-text.
- Strip any storage-only syntax from the body before matching/snippeting (in the
  reference app, lines like `{tab: name}` used as invisible separators are removed so
  they never match or appear in snippets). Adapt this to your app's own hidden syntax.

---

## 5. Results rendering & interaction

### 5.1 Each result shows
- **Name/title** of the matched item (escaped).
- A **location label** describing where the item lives in the app, e.g.
  `Column` or `Column › Section`, and for a nested item append `· ParentName`.
  Separators are rendered as subtle glyphs (`›`, `·`).
- A **snippet** line (only if a snippet exists) with the matched substring wrapped in a
  highlight (`<mark>`), all other text escaped.

### 5.2 Empty state
- If a non-empty query yields nothing, show a centered "No matches found" message.

### 5.3 Keyboard navigation
- In the **input**:
  - `Enter` → run search.
  - `ArrowDown` → move focus to the first result.
- In the **results list**:
  - `ArrowDown` / `ArrowUp` → move focus between results.
  - `ArrowUp` past the first result → return focus to the input.
  - `Enter` on a focused result → activate it (native button behavior).
- Each result is a real `<button>` so focus, Enter, and screen-reader semantics work
  for free.

### 5.4 Activation
- Clicking or Enter-ing a result:
  1. closes the search dialog,
  2. opens that item in the app's existing **detail/edit view**, passing the item's
     path/identifier (and a flag if it is a nested/child item).

---

## 6. Data adapter (the ONLY app-specific part)

Everything above is generic. To port the dialog, implement these four seams against
your app's data shape. Keep names/signatures similar so the rest stays unchanged.

1. **`isItem(node)` → bool**
   Decide whether a tree node is a searchable item vs. a structural container.
   Reference approach: an object is an item if it has any of a known set of item fields
   (e.g. `state`, `desc`, `tags`, `due`, `prio`, …). Define the equivalent field set for
   your data.

2. **`matchField(name, item, query)` → { snippet } | null**
   Apply the priority match (description → tags → name), return a snippet for the first
   hit, or `null`. Include nested-child matching here or in the walk.

3. **`describeLocation(pathSegments)` → { column, section }`**
   Turn a node's path into human-readable location text. This is entirely domain
   specific (in the reference app it maps `firstCol/current`, `YYYY/MM`, "Following
   Years", etc. to friendly labels). Return whatever columns/sections your UI uses.

4. **`buildPath(match)` → identifier**
   Produce the identifier your detail/edit view expects to open the item (in the
   reference app: a dot-joined path with the item name encoded, and a `.sub tasks.`
   segment for children). Match your own routing/opening convention.

Also provide:
- **`openDetail(identifier, isChild)`** — your existing "open item" entry point.
- The **in-memory dataset** to walk (the reference reads `controller.appData`).

---

## 7. Suggested structure (mirrors the reference app)

- **Markup**: a modal partial with `#searchModal` → `.modal-content` → header
  (title + close) → body (input bar with `#searchInput` + go button, and
  `#searchResults` container).
- **Logic**: a `SearchManager` class holding a reference to the app controller, with:
  `open()`, `close()`, `isOpen()`, `handleInputKeydown()`, `handleResultsKeydown()`,
  `runSearch()`, and private `walk()`, `matchField()`, `snippet()`,
  `describeLocation()`, `buildPath()`, `render()`, `highlight()`.
- **Wiring** (in the app's main controller): open on `Ctrl/Cmd+K` and on the menu
  button; close on `Esc`, backdrop click, and close button; bind input/results keydown
  handlers; the go button calls `runSearch()`.

---

## 8. Acceptance checklist

- [ ] `Ctrl/Cmd+K` opens the dialog and focuses the input; `Esc` / backdrop / `×` close it.
- [ ] Enter or the go button runs the search; empty query clears results.
- [ ] Matches description, tags, and name (first-match priority) — including nested items.
- [ ] Each result shows name, location label, and a highlighted snippet.
- [ ] "No matches found" appears for a non-empty query with zero hits.
- [ ] Arrow keys move between input and results; Enter/click opens the item and closes search.
- [ ] No network request is made to perform the search.
- [ ] Works and is comfortably tappable on mobile.

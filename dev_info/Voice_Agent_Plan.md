# AI Voice Agent — Implementation Plan

Gemini Live voice agent for Nutri Counter. Activated from `#sidebar` / `#bottomNav`.
First tool: find a food and jump to it in the food grid.

Source material: `_agent_sample/` (voice agent from the homepage project). Base file is
**`_agent_sample/voice-agent/agent.js.v6.bak`** — the direct-to-Google variant that builds
the Live setup message and handles tool calls in the browser. The newer `agent.js` is only
a thin client for a Cloudflare relay and is **not** our base (we have no Worker).


## 1. Verified facts (checked before planning)

### Tool use with Gemini Live works

From https://ai.google.dev/gemini-api/docs/live-api/tools

| | `gemini-3.1-flash-live-preview` | `gemini-2.5-flash-native-audio-preview` |
|---|---|---|
| Function calling | Supported (synchronous only) | Supported (sync + async `NON_BLOCKING`) |
| Google Search | Supported | Supported |

- Declarations must be sent in the **`setup` message** — all tools at session start.
- Model emits `toolCall.functionCalls[]`; the client **must** answer manually with
  `toolResponse.functionResponses[]`. There is no automatic tool handling (unlike
  `generateContent`).
- Search tools cannot be combined with function calling in one setup. Not relevant here.
- Proven in the sample: `navToolDeclaration()` / `handleToolCall()` in
  `_agent_sample/voice-agent/agent.js.v6.bak` (lines ~496, ~1555).

### Decisions taken

| Topic | Decision |
|---|---|
| Auth | Ephemeral token (real key stays server-side) |
| Model | `gemini-3.1-flash-live-preview` |
| Mic format | `realtimeInput.audio` (the 3.1 format; `mediaChunks` gives close code 1007) |
| Button position | `#sidebar` above `bi-info-circle` **and** `#bottomNav` (mobile) |
| Agent UI | None. The sidebar button *is* the mic switch. No overlay, no transcript, no nav toggle |


## 2. Architecture

```
Browser --(1) ajax 'getAgentToken' --> PHP --> POST /v1beta/auth_tokens
                                                 (x-goog-api-key: GEMINI_API_KEY from .env)
        <-- { token, model, systemInstruction }
        --(2) wss://generativelanguage.googleapis.com/ws/
              google.ai.generativelanguage.v1beta.GenerativeService.BidiGenerateContent
              ?access_token=TOKEN                --> Gemini Live   (direct, no proxy)
```

PHP cannot proxy a WebSocket, so the browser must connect to Google directly. Ephemeral
tokens are Google's documented answer for that case: single-use, 30 min lifetime, and the
long-lived API key never reaches the browser.

Token request (https://ai.google.dev/gemini-api/docs/live-api/ephemeral-tokens):

```
POST https://generativelanguage.googleapis.com/v1beta/auth_tokens
x-goog-api-key: <GEMINI_API_KEY>
Content-Type: application/json

{
  "uses": 1,
  "expireTime": "<now + 30 min, ISO8601 Z>",
  "newSessionExpireTime": "<now + 1 min, ISO8601 Z>"
}
```

Response contains `name` — that string is used in place of the API key.
Note: it goes in `?access_token=`, not `?key=`.


## 3. Files

### New

| File | Purpose |
|---|---|
| `.env` | `GEMINI_API_KEY=...` — gitignored |
| `.env.example` | Same key, empty value, committed |
| `.htaccess` (project root) | Denies dotfiles, so `/.env` is not readable when the project root is the doc root |
| `src/lib/env.php` | Standalone `env_get( $key, $default = null)`; reads + caches `.env` |
| `src/ajax/get_agent_token.php` | `trait GetAgentTokenAjaxController` → `getAgentToken( $request )` |
| `src/data/agent/prompt.md` | System instruction, server-side and editable |
| `src/VoiceAgentController.js` | The agent (ported + trimmed from `agent.js.v6.bak`) |
| `src/style/agent.css` | Mic button states |

### Modified

| File | Change |
|---|---|
| `src/view/-this.php` | `<li>` in `#sidebar` above the info-circle item; `<a>` in `#bottomNav`; `agent.css` link; `VoiceAgentController.js` tag; `voiceCrl = new VoiceAgentController()` in `ready()` |
| `src/MainController.js` | Extract public `findFoods( query )` + `jumpToFood( rec )`; rewire `runSearch()` / `searchResultClick()`; `openSearch( event, query )` takes an optional prefill |
| `src/AppController.php` | Add `use GetAgentTokenAjaxController;` to the trait list (~line 34) |
| `src/config.yml` | New `agent:` block |
| `.gitignore` | Add `.env` |

`src/AppController.php` auto-requires everything in `ajax/` via `scandir` (line 17-18), so
only the `use` line is needed.


## 4. Server side

### `src/lib/env.php`

Standalone function (snake_case, per the PHP rules), no dependency:

```php
function env_get( $key, $default = null )
```

Parses `KEY=value` lines from the repo-root `.env`, skips blanks and `#` comments, strips
surrounding quotes, caches in a static. Never echoes values.

The `.env` lives one level above `src/`, so it is outside the doc root when `src/` is served
directly. Because the project has no server config at all and the doc root may well be the
project root, a root `.htaccess` denies dotfiles as a second line of defence (Apache 2.4
`Require all denied`; harmless on other servers, but then it protects nothing — check your
setup).

**Gotcha:** an empty `GEMINI_API_KEY=` yields `''`, not `null`, so the `$default` argument
does not kick in. The ajax endpoint must test with `empty()`.

### `src/config.yml` — new block

```yml
agent:                        # AI voice agent (Gemini Live)

  enabled: true               # false hides the sidebar/bottom-nav button
  model:   "gemini-3.1-flash-live-preview"
  voice:   "Aoede"            # Gemini Live prebuilt voice
```

### `src/ajax/get_agent_token.php`

Follows the existing trait pattern (see `src/ajax/get_charts_data.php`):

```php
trait GetAgentTokenAjaxController
{
  public function getAgentToken( $request )
  {
    // 1. read GEMINI_API_KEY via env_get, error out if missing
    // 2. curl POST to auth_tokens with uses/expireTime/newSessionExpireTime
    // 3. return result => 'success', data => { token, model, voice, systemInstruction }
  }
}
```

- HTTP via curl (per the PHP rules; no Guzzle needed for one call).
- `systemInstruction` = `file_get_contents('data/agent/prompt.md')` plus today's date
  appended, so the model knows "today".
- On failure return `['result' => 'error', 'message' => ...]` — never leak the key or the
  raw Google response body into the message.
- Called as `ajax.send('getAgentToken', {}, cb)` (see `ChartsController.js` line 34 for the
  calling convention).

### `src/data/agent/prompt.md`

Short persona + rules. Content:

- Who: assistant inside a nutrition counter app, helps the user find and navigate foods.
- Style: brief spoken answers, no markdown, the user's language.
- Tools: describe `findFood` and `openFoodSearch` and when to use each — in particular:
  on several matches either ask which one, or use `openFoodSearch` when the user wants to
  see all of them.
- Do not invent foods that are not in the grid.


## 5. Client side — `src/VoiceAgentController.js`

Class `VoiceAgentController`, instantiated as global `voiceCrl` in `src/view/-this.php`,
matching the existing controller pattern (`mainCrl`, `widgetsCrl`, `chartsCrl`, `dropMenu`).

### Public surface

```
toggle( event )   // bound; wired to both buttons — start when idle, stop otherwise
start()           // token -> audio contexts -> mic -> socket -> setup -> capture
stop()            // close socket, stop tracks, close both audio contexts, reset state
```

### MUST keep from the sample (mobile-critical — verified working on phones)

Do **not** "modernise" these. They are the reason the sample works on smartphones as well
as desktops.

1. **Two separate AudioContexts** (`agent.js.v6.bak` lines 311-318)
   - capture: `new AudioContext({ sampleRate: 16000 })` — Gemini Live needs 16 kHz mic input
   - playback: `new AudioContext()` at the hardware default rate, so the 24 kHz model audio
     is resampled exactly once. Reusing the 16 kHz context downsamples the output and
     wastes CPU on every chunk.
   - `window.AudioContext || window.webkitAudioContext` — keep the webkit fallback.

2. **Resume both contexts if `state === 'suspended'`** (lines 320-324), and do it inside the
   click-handler call chain. iOS/Safari only allows this from a user gesture; that is why
   `start()` must be reached synchronously from the button click and must not be deferred
   behind anything that breaks the gesture chain.

3. **`createScriptProcessor(4096, 1, 1)`** for capture (line 702). Deprecated but works
   everywhere including iOS Safari. **Do not replace with AudioWorklet** — that would need
   a separate module file and has not been tested on the target phones.
   - `source.connect(processor)` and `processor.connect(audioContext.destination)` are both
     required or `onaudioprocess` never fires in some browsers.

4. **Float32 → Int16 conversion and base64** exactly as in lines 707-739, sending
   `{ realtimeInput: { audio: { mimeType: 'audio/pcm;rate=16000', data: base64 } } }`.
   Guard on `ws.readyState === OPEN && state === 'listening'`.

5. **Gapless playback scheduler**: `scheduleAudioChunk()`, `playScheduled()`,
   `pcmToAudioBuffer()`, plus `nextStartTime`, `activeSources`, `scheduleLead = 0.08`
   (lines 68-72, 744-860). Chunks are placed back-to-back on the playback clock so decode
   jitter on slow mobile CPUs does not create gaps. Parse the rate from the mime type
   (`rate=(\d+)`, default 24000) instead of hardcoding.

6. **Barge-in**: stop and drop scheduled sources on `interrupted`, reset `nextStartTime`.

7. **Errors must be visible without devtools.** On a phone there is no console, so every
   failure has to reach the screen — see the UI section. Keep the WebSocket `onclose`
   handling that reports non-1000 close codes with code + reason (lines 554-563); 1007 /
   1008 / 1011 are how a wrong model or bad setup shows up.

8. **`getUserMedia({ audio: true })`** — plain constraints, no `sampleRate` in there
   (phones reject or silently ignore exotic constraints).

9. Friendly mapping of mic errors (`NotAllowedError`, `NotFoundError`, …) — port
   `getMicErrorMessage()`.

### Drop from the sample

de/en i18n and the `t` table, resume/handoff + `sessionStorage` (`va:*` keys),
`teardownConnection()`, sitemap/`navTargets`/`navigate`, `runtimeInstructionNote()`,
use-case finder, `sidebar.js` host, launcher styles, "Testbetrieb" badge, transcript
rendering, footer toggles, `use_legacy_input` (fixed to the 3.1 format), `simulateMic`
(optional — cheap to keep and useful on a PC without a mic; keep it behind a config flag).

Target size ~400-500 lines.

### Setup message

```js
{
  setup: {
    model: `models/${model}`,
    generationConfig: {
      responseModalities: ['AUDIO'],
      speechConfig: { voiceConfig: { prebuiltVoiceConfig: { voiceName: voice } } }
    },
    systemInstruction: { parts: [{ text: systemInstruction }] },
    tools: [{ functionDeclarations: [ findFoodDeclaration(), openFoodSearchDeclaration() ] }]
  }
}
```

Send it as the first frame in `ws.onopen`. Then the greeting prompt (port
`sendGreetingPrompt()`), then start capture.


## 6. Tools

### Declarations

```js
{
  name: 'findFood',
  description: 'Find a food in the user\'s food grid and jump to it. Use when the user asks '
    + 'where a food is or asks to be taken to it.',
  parameters: {
    type: 'OBJECT',
    properties: {
      name: { type: 'STRING', description: 'The food name as the user said it' },
      tab:  { type: 'STRING', description: 'Optional: pick one occurrence when the food sits on several tabs' }
    },
    required: ['name']
  }
}

{
  name: 'openFoodSearch',
  description: 'Open the food search overlay prefilled with a query, so the user sees all '
    + 'matches at once. Use when several foods match and the user wants to see them all.',
  parameters: {
    type: 'OBJECT',
    properties: { query: { type: 'STRING' } },
    required: ['query']
  }
}
```

### Handling (`handleToolCall`)

```
findFood:
  matches = mainCrl.findFoods( args.name )
  if args.tab -> keep only records whose tabLabel matches it (case insensitive)
  0   -> { result: 'none' }
  1   -> mainCrl.jumpToFood( matches[0] )
         { result: 'jumped', food, tab, group }
  2+  -> { result: 'multiple', matches: [{ food, tab, group }] }   // model asks which one

openFoodSearch:
  mainCrl.openSearch( null, args.query )   // opens the modal and runs the search
  { result: 'opened' }
```

Then always:

```js
this.ws.send(JSON.stringify({
  toolResponse: { functionResponses: [{ id: fc.id, name: fc.name, response: { result } }] }
}))
```

Returning the match list (rather than resolving it silently) is what makes "ask the user
which one" work naturally — same pattern as the sample's multi-target `navigate`.

Only send DOM-safe, serialisable fields to the model — never `itemEl` / `navLink`.


## 7. `src/MainController.js` refactor

Today the search logic is private and only reachable from the modal:

- `#buildFoodIndex()` (line ~560) — walks `#layout .layout-item`, returns one record per
  (food, tab): `{ food, itemEl, navLink, tabLabel, groupName }`
- `runSearch()` (line ~506) — filters the index, renders the result buttons
- `searchResultClick()` (line ~538) — hides the modal, then on `hidden.bs.modal` clicks the
  tab link, `scrollIntoView`, `#flashItem`

Changes:

1. **New public `findFoods( query )`** — `#buildFoodIndex()` + the substring filter.
   Used by `runSearch()` and by the agent. Returns the full records (with elements); the
   agent strips them before answering the model.

2. **New public `jumpToFood( rec )`** — the tab click + `scrollIntoView` + `#flashItem`
   block currently inlined in `searchResultClick()`. Called from `searchResultClick()`
   (after the modal closed) and directly from the agent.

3. **`openSearch( event, query = '' )`** — when a query is given, prefill `#searchInput` and
   run the search once the modal is shown. Careful: the existing `show.bs.modal` handler
   clears `#searchInput` and `#searchResults`, so the prefill must happen on
   `shown.bs.modal` (or after `show()`), not before.

4. `searchResultClick()` keeps the `hidden.bs.modal` deferral — the target pane must be
   visible before scrolling. When the agent jumps with the modal closed, no deferral is
   needed.

No behaviour change for the existing search; both new methods have two callers, so nothing
redundant is introduced.


## 8. UI — the button is the whole interface

`src/view/-this.php`, `#sidebar` bottom list, **above** the info-circle `<li>` (line ~62):

```php
<li class="nav-item">
  <a id="agentBtn" onclick="voiceCrl.toggle(event)" class="nav-link" href="#" title="Voice assistant">
    <i class="bi bi-mic"></i>
  </a>
</li>
```

Same in `#bottomNav` (line ~101, no `<li>` wrapper there) with `id="agentBtnMobile"`, so the
agent is reachable on phones. Both wired to `voiceCrl.toggle(event)`; the controller keeps
a list of its buttons and updates all of them together.

Wrap both in `<?php if( config::get('agent.enabled') ): ?>`.

### States

| State | Icon | Style |
|---|---|---|
| idle | `bi-mic` | default nav-link |
| connecting | `bi-mic` | gold, pulsing |
| listening | `bi-mic-fill` | orange-red, pulsing |
| speaking | `bi-mic-fill` | gold |
| error | `bi-mic-mute-fill` | red |

`src/style/agent.css` — one `.agent-btn` base plus `.state-connecting` / `.state-listening`
/ `.state-speaking` / `.state-error` and a `@keyframes` pulse. Accent colours match the
existing theme (orange `#e95420`-ish, gold); no blue.

Also set `title` and `aria-label` to the current state, so there is a text hint on hover
and for screen readers.

### Errors on screen (required for mobile)

Reuse the existing tooltip helper instead of a new UI:

```js
showOverlayInfo( eventLikeObj, { tooltipId: 'agent-error', position: 'auto', closeOnClick: true })
```

`src/lib/overlay_MOV.js` reads the message from the trigger's `data-content`, so set
`data-content` on the button before calling it. Auto-hide after ~6 s, then back to idle.
No `alert()` (forbidden by the rules).


## 9. Verification

Automated (what I can run):

- `php -l` on every new/changed PHP file
- `node --check` on `VoiceAgentController.js` and `MainController.js`
- with a real key in `.env`: curl the `auth_tokens` endpoint and confirm a `name` comes back
- with the app running: `ajax.send('getAgentToken', ...)` returns `result: 'success'` and a
  token, and the existing food search still works unchanged (regression check on the
  refactor)

Manual (needs the user):

- desktop: click the mic, allow the microphone, hear the greeting, say "Show me where Tofu
  is", verify the grid jumps and the row flashes
- ambiguous name → the agent asks which one; "show me all" → the search overlay opens
  prefilled
- smartphone: same flow from `#bottomNav`, verify mic permission, audio playback and that
  errors appear as a tooltip

Constraint: `getUserMedia` requires `localhost` or HTTPS. Fine locally; over plain HTTP on
the LAN the mic is blocked by the browser.


## 10. Order of work

1. `.env`, `.env.example`, `.gitignore`, `src/lib/env.php`, `config.yml` agent block — **done**
2. `src/ajax/get_agent_token.php` + `AppController.php` use line + `data/agent/prompt.md`
   — **done**, token round-trip verified against the live api before any client code
3. `src/MainController.js` refactor — **done**, existing search confirmed working
4. `src/VoiceAgentController.js` + tools + buttons + `src/style/agent.css` — **done**

Steps 4-6 of the original order were merged: the controller cannot be exercised at all
without the buttons, so a voice-only intermediate would have cost a verification round
without being testable.

Remaining: manual testing on desktop and phone (section 9).


## 11. Open points

- `expireTime` max is capped by the API; if 30 min is rejected, lower it. A session also
  needs `sessionResumption` to reconnect every 10 min within `expireTime` — out of scope
  for v1 (a call longer than 10 min will simply end; the user can press the button again).
- `liveConnectConstraints` could pin the model and system instruction into the token
  server-side. Not needed for a self-hosted personal app; noted as a later hardening step.
- Later tools worth adding: add an entry to the day, read back today's totals, switch the
  day. Deliberately out of scope for v1.

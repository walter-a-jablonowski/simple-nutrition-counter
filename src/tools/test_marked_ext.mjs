/*

Standalone tests for the bundle markdown extensions (see lib/marked_ext.js).

Run from the `src` directory:  node tools/test_marked_ext.mjs

Offline, no dependencies beyond the marked bundle already in lib. The extension file
expects a global `marked`, the way the browser has it, so it is loaded through
new Function - same trick as tools/test_voice_entries.mjs.

*/

import { readFileSync } from 'fs'
import { createRequire } from 'module'

const require = createRequire( import.meta.url )
const marked  = require('../lib/marked.min.js')

globalThis.marked = marked

new Function( readFileSync('lib/marked_ext.js', 'utf8'))()

let pass = 0
let fail = 0

function check( name, ok, detail = '' )
{
  if( ok ) { pass++; console.log(`  PASS  ${name}`) }
  else     { fail++; console.log(`  FAIL  ${name}${detail ? `  (${detail})` : ''}`) }
}

// 1) A note becomes its own block and keeps its markdown

let html = marked.parse('::: note\nFood list is **made** in a way\n:::\n')

check('note wrapper',   html.includes('<div class="md-note">'), html)
check('note content',   html.includes('<strong>made</strong>'), html)
check('note closes',    (html.match(/<div class="md-note">/g) || []).length === 1)

// 2) A fold becomes a list-group with a collapse target, and the ids are unique

html = marked.parse('::: fold Foods to use\nsome text\n:::\n\n::: fold Second\nmore\n:::\n')

const ids = [ ...html.matchAll(/id="(mdFold\d+)"/g)].map( m => m[1])

check('fold wrapper',      html.includes('<div class="list-group md-fold">'), html)

// A ul here would collect the no-indent class renderMarkdown adds, and that is a
// 20px padding rather than a reset - it shifted the whole block right

check('fold is no list',   ! /<ul[^>]*md-fold/.test( html), html)
check('fold title',        html.includes('Foods to use'))
check('fold toggle',       html.includes('data-bs-toggle="collapse"'))
check('two folds',         ids.length === 2, ids.join(', '))
check('unique fold ids',   ids[0] !== ids[1], ids.join(', '))
check('toggle points at its own body', html.includes(`href="#${ids[0]}"`) && html.includes(`href="#${ids[1]}"`))

// 3) Blocks nest: a note inside a fold, and the fold does not stop at the note's fence

html = marked.parse('::: fold Outer\n::: note\ninner note\n:::\n\nafter the note\n:::\n')

check('nested note is inside the fold', html.indexOf('md-note') > html.indexOf('md-fold'), html)
check('fold keeps what follows the note', html.includes('after the note'), html)
check('only one fold',  (html.match(/md-fold"/g) || []).length === 1, html)

// 4) A table inside a fold survives - that is what conceptMisc is made of

html = marked.parse('::: fold Foods\n| a | b |\n| --- | --- |\n| Avoid | sugar |\n:::\n')

check('table inside a fold', html.includes('<table>') && html.includes('Avoid'), html)

// 5) An unclosed fence is left to the normal tokenizers rather than eating the rest

html = marked.parse('::: fold Never closed\nsome text\n')

check('unclosed block is not swallowed', ! html.includes('md-fold'), html)
check('unclosed block keeps its text',   html.includes('some text'), html)

// 6) A title is text, not markup

html = marked.parse('::: fold <script>x</script>\nbody\n:::\n')

check('title is escaped', ! html.includes('<script>'), html)

// 7) Plain markdown is untouched by the extension

html = marked.parse('**bold** and [2](#ref2)\n\n- one\n- two\n')

check('bold still works', html.includes('<strong>bold</strong>'))
check('ref link still works', html.includes('href="#ref2"'))
check('list still works', html.includes('<li>'))

console.log(`\n  ${pass} passed, ${fail} failed`)

process.exit( fail ? 1 : 0)

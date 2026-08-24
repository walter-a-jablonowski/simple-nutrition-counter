/*

Markdown extensions for the bundle texts (marked).

A bundle's diet rules (-this.yml: framework, conceptMisc, goals, sampleMenu) used to be
html, because three things in them have no markdown syntax: a foldable block, a coloured
note, and a link that switches a bootstrap tab. Everything else in there - bold, lists,
two column tables, the (1) (2) reference links - is plain markdown and needs nothing.

Self registering: loading this file after marked.min.js is all the wiring there is.

  ::: fold Foods to use          list-group header that folds the block below it
    ...
  :::

  ::: note                       a coloured aside
    ...
  :::

The tab link is not here but in MainController.renderMarkdown(), which post processes
the html anyway:

  [(use a jug)](tab:pane/target)

Blocks nest - a note sits inside the fold in the sample menu - so the tokenizer counts
opens and closes instead of stopping at the first ":::".

*/

(function() {

  let foldId = 0;   // collapse targets have to be unique on the page

  const bundleBlock = {

    name:  'bundleBlock',
    level: 'block',

    // Cheap check so marked only calls the tokenizer where a block can start

    start( src ) {
      return src.match(/^:::/m)?.index;
    },

    tokenizer( src ) {

      const open = /^::: *([A-Za-z][\w-]*) *([^\n]*)(?:\n|$)/.exec( src );

      if( ! open )
        return;

      // Walk the lines, keeping count, so a nested block closes itself and not us

      let pos   = open[0].length;
      let depth = 1;
      let body  = '';

      while( pos < src.length && depth )
      {
        const nl   = src.indexOf('\n', pos);
        const line = src.slice( pos, nl === -1 ? src.length : nl);

        if(      /^::: *[A-Za-z]/.test( line))  depth++;
        else if( /^::: *$/.test( line))         depth--;

        pos = nl === -1 ? src.length : nl + 1;

        if( depth )
          body += line + '\n';
      }

      if( depth )
        return;   // never closed, so it is not one of ours - let the other tokenizers have it

      return {
        type:   'bundleBlock',
        raw:    src.slice(0, pos),   // exactly what was consumed, or marked loses its place
        kind:   open[1].toLowerCase(),
        title:  open[2].trim(),
        tokens: this.lexer.blockTokens( body, [])
      };
    },

    renderer( token ) {

      const body = this.parser.parse( token.tokens );

      if( token.kind === 'fold' )
      {
        const id = 'mdFold' + (++foldId);

        return '<ul class="list-group md-fold">'
             +   '<li class="list-group-item md-fold-head d-flex justify-content-between align-items-center">'
             +     escape( token.title )
             +     `<a data-bs-toggle="collapse" href="#${id}" class="text-body-secondary" role="button">`
             +       '<i class="bi bi-arrow-down-circle"></i>'
             +     '</a>'
             +   '</li>'
             +   `<li id="${id}" class="list-group-item collapse">${body}</li>`
             + '</ul>';
      }

      if( token.kind === 'note' )
        return `<div class="md-note">${body}</div>`;

      return body;   // an unknown kind still shows its content, it just loses the wrapper
    }
  };

  // Titles come out of the user's own yml, so they are text, never markup

  function escape( text ) {
    return text.replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[c]);
  }

  marked.use({ extensions: [ bundleBlock ] });

})();

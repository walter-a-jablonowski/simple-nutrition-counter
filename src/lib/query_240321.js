/*@

query()

RETURNS:

  - single if unique tag or id
  - or multiple

*/
function query( sel, returnSingle = false ) /*@*/
{
  sel = sel.replace(/\s+/g, ' ').trim();
  let r = document.querySelectorAll( sel );

  if(['html', 'title', 'head', 'body', 'main'].includes(sel))  // AI: html, head, title, body, main are structurally unique and should only appear once
    return r[0];                                               // typically once, but may be used multiple: header, nav, main, footer, article, section, aside
  else if( sel.charAt(0) == '#' && sel.indexOf(' ') === -1 )   // #id only
    return r[0];
  else
  {
    if( returnSingle && r.length === 1 )
      return r[0];
    else
      return r;
  }
}


/*@

alternative: query()[0]

*/
function queryOne( sel ) /*@*/
{
  return query( sel, true );
}


/*@

synonym

*/
function queryFirst( sel ) /*@*/
{
  return query( sel, true );
}


/*@

Find inside version of query()

*/
HTMLElement.prototype.find = function( sel, returnSingle = true ) /*@*/
{
  sel = sel.replace(/\s+/g, ' ').trim();     // same as query()
  let r = document.querySelectorAll( sel );

  if(['html', 'title', 'head', 'body', 'main'].includes(sel))
    return r[0];
  else if( sel.charAt(0) == '#' && sel.indexOf(' ') === -1 )
    return r[0];
  else
  {
    if( returnSingle && r.length === 1 )
      return r[0];
    else
      return r;
  }
}

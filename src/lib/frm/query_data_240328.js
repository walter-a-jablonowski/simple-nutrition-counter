function queryData( sel, attribs )  // keeping this low level
{
  // const parentElement = document.querySelector(sel)
  const elems = document.querySelectorAll(sel)
  if( ! elems) return []
  
  // const children = parentElement.children
  const results = []

  // for( const child of children )
  for( const elem of elems )
  {
    const result = {}
  
    for( const attrib of attribs )
    {
      let value = elem.getAttribute(`data-${attrib}`)
      if( value )
      {
        try {
          result[attrib] = JSON.parse( value.replace(/\\/g, ''))  // try to parse JSON
          // result[attrib] = JSON.parse( decodeHtml(value))      // TASK: remove htmlspecialchars() and just escape " or use this alternative
        } catch(error) {
          result[attrib] = value                                  // or is scalar value
        }
      }
    }

    if( Object.keys(result).length > 0)
      results.push(result)
  }
  
  return results
}

// function decodeHtml(html)
// {
//   return html.replace(/&quot;/g, '"')
//              .replace(/&amp;/g, '&')
//              .replace(/&lt;/g, '<')
//              .replace(/&gt;/g, '>')
//              .replace(/&apos;/g, "'")
// }

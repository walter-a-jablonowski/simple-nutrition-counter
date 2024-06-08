function queryData( sel, attributes)
{
  const parentElement = document.querySelector(sel)  // keeping this low level
  if( ! parentElement) return []
  
  const children = parentElement.children
  const results = []

  for( const child of children )
  {
    const result = {}
  
    for( const attribute of attributes)
    {
      let value = child.getAttribute(`data-${attribute}`)
      if( value )
      {
        try {
          result[attribute] = JSON.parse( value.replace(/\\/g, ''))  // try to parse JSON
          // result[attribute] = JSON.parse( decodeHtml(value))      // TASK: remove htmlspecialchars() and just escape " or use this alternative
        } catch(error) {
          result[attribute] = value                                  // or is scalar value
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

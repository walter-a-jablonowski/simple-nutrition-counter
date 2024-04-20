function send( identifier, data, callback, ajaxFile = 'ajax.php')
{
  data = {
    identifier: identifier,
    ...data
  }

  fetch( ajaxFile, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify( data )
  })
  .then( response => response.text())
  .then( result => {
    result = JSON.parse(result)
    if( typeof callback === 'function' )
      callback( result.result, result.data  || null )
  })
  .catch( result => {
    result = JSON.parse(result)
    if( typeof callback === 'function' )
      callback('error', result.data || null)
  })
}

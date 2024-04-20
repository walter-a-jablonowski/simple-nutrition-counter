class ajax
{

  static file = 'index.php'  // default, change like `ajax.file = 'ajax.php'`

  static send( identifier, data, callback)
  {
    data = {
      identifier: identifier,
      ...data
    }

    fetch( ajax.file, {
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
}

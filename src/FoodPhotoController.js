/*

Photo import: turn pictures of a packaging into a new food.

Owns the shot list and the upload, nothing else — the extracted food goes to
mainCrl.applyImportedFood(), the same way the page import does.

Pictures are downscaled in the browser before they are sent. A phone shot is
4000x3000 and several MB, base64 adds another third, and the model resizes
anything bigger than its tiles anyway: uploading the original would only cost
minutes of mobile data and risk php's post_max_size.

*/
class FoodPhotoController
{

  constructor()
  {
    const panel = query('#newEntryPhotoPanel')

    if( ! panel )  return   // photoImport off: the markup is not rendered

    this.maxImages = parseInt( panel.dataset.maxImages ) || 3
    this.maxEdge   = parseInt( panel.dataset.maxEdge )   || 1568
    this.quality   = parseFloat( panel.dataset.quality ) || 0.82
    this.maxBytes  = 8 * 1024 * 1024        // the server rejects more than this

    this.shots = []

    // Two inputs: `capture` opens the camera directly but hides the gallery on
    // android, so "Choose" is the one that works on a desktop with saved files

    query('#photoCameraInput').event('change', event => this.#addFiles( event.target ))
    query('#photoFileInput').event('change',   event => this.#addFiles( event.target ))
  }


  reset()
  {
    if( ! this.shots )  return

    this.shots = []
    this.#renderList()

    query('#photoMsg').textContent = ''
  }


  // Send the shots off to be read. Kept separate from the ajax callback so a
  // failed import keeps the pictures - re-shooting a pack would be infuriating

  run()
  {
    const msg = query('#photoMsg')
    const btn = query('#photoRunBtn')

    msg.textContent = ''

    if( ! this.shots.length ) {
      msg.textContent = 'Take at least one picture.'
      return
    }

    const bytes = this.shots.reduce(( sum, shot) => sum + shot.bytes, 0)

    if( bytes > this.maxBytes ) {
      msg.textContent = 'The pictures are too large together. Remove one and try again.'
      return
    }

    btn.disabled    = true
    btn.textContent = 'Reading …'

    // The model needs 10-40 s, so say something before the user gives up

    const watchdog = setTimeout(() => msg.textContent = 'Still reading, this can take a minute …', 20000)

    ajax.send('importFoodPhotos', { images: this.shots.map( shot => shot.base64) }, (result, data) => {

      clearTimeout( watchdog )

      btn.disabled    = false
      btn.textContent = 'Read pictures'
      msg.textContent = ''

      if( result !== 'success') {
        msg.textContent = (data && data.message) || 'Could not read the pictures'
        return
      }

      mainCrl.applyImportedFood( data.food, data.warnings)
    })
  }


  // Downscale right away, so the thumbnail confirms the shot is usable and the
  // "Read pictures" tap is instant

  async #addFiles( input )
  {
    const files = Array.from( input.files || [])

    input.value = ''   // same file can be picked again after a remove

    const msg = query('#photoMsg')
    const btn = query('#photoRunBtn')

    msg.textContent = ''

    if( this.shots.length + files.length > this.maxImages ) {
      msg.textContent = `At most ${this.maxImages} pictures.`
      return
    }

    btn.disabled    = true
    btn.textContent = 'Preparing …'

    for( const file of files )
    {
      try {
        this.shots.push( await this.#downscale( file ))
      }
      catch( error ) {
        msg.textContent = error.message
      }
    }

    btn.disabled    = false
    btn.textContent = 'Read pictures'

    this.#renderList()
  }


  /* One picture -> a downscaled jpeg as base64.

     The canvas is not just for the resize: iphones shoot heic, which the model
     does not take, and re-encoding normalizes that. It also drops the exif block,
     so no gps coordinates travel with a picture taken at home. */

  async #downscale( file )
  {
    let bitmap = null
    let image  = null
    let url    = null

    /* imageOrientation applies the exif rotation to the pixels. drawImage writes
       the raw buffer, and a portrait phone shot is usually stored landscape with
       a rotation tag - ignoring it sends the nutrition table lying on its side,
       which is the most likely reason for a bad reading */

    if( window.createImageBitmap )
      bitmap = await createImageBitmap( file, { imageOrientation: 'from-image' })
    else {
      url       = URL.createObjectURL( file )   // a pointer, unlike readAsDataURL
      image     = new Image()
      image.src = url
      await image.decode()
    }

    const source = bitmap || image
    const width  = source.width  || source.naturalWidth
    const height = source.height || source.naturalHeight

    if( ! width || ! height )
      throw new Error('This picture could not be read (unsupported format?)')

    const scale  = Math.min( 1, this.maxEdge / Math.max( width, height))   // only ever shrink
    const canvas = document.createElement('canvas')

    canvas.width  = Math.round( width  * scale )
    canvas.height = Math.round( height * scale )

    const context = canvas.getContext('2d')

    context.imageSmoothingQuality = 'high'
    context.drawImage( source, 0, 0, canvas.width, canvas.height)

    if( bitmap )  bitmap.close()
    if( url )     URL.revokeObjectURL( url )

    const dataUrl = canvas.toDataURL('image/jpeg', this.quality)
    const base64  = dataUrl.slice( dataUrl.indexOf(',') + 1)

    return { dataUrl: dataUrl, base64: base64, bytes: Math.round( base64.length * 3 / 4) }
  }


  #renderList()
  {
    const list = query('#photoList')

    list.innerHTML = ''

    this.shots.forEach(( shot, index) => {

      const item = document.createElement('div')

      item.className = 'photoThumb'
      item.innerHTML = `<img src="${shot.dataUrl}" alt="">`
                     + '<button class="btn-close" type="button" aria-label="Remove"></button>'

      item.querySelector('.btn-close').event('click', () => {
        this.shots.splice( index, 1)
        this.#renderList()
      })

      list.appendChild( item )
    })
  }
}

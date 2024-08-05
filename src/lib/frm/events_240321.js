function ready( fn )
{
  if( document.readyState !== 'loading' )
    fn();
  else
    document.addEventListener('DOMContentLoaded', fn);
}

function event( type, fn )
{
  document.addEventListener(type, fn)
}

HTMLElement.prototype.event = function( type, fn )
{
  this.addEventListener(type, fn)
}

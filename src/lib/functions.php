<?php

/**
 * Helper function to check if an array is associative
 * 
 * @param array $array
 * @return bool
 */
function is_assoc_array( $array )
{
  if( ! is_array($array))
    return false;
    
  return array_keys($array) !== range(0, count($array) - 1);
}

?>

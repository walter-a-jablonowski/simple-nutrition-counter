<?php

  public function count( string $key )
  {
    $elem = &$this->findKey( $key );  // TASK: Problem: works only for existing keys
                                      // has own impl cause we can't retrun null from a findKey() function when byref
    if( ! is_array($elem))
      throw new \Exception("$key is no array");

    return count( $elem );
  }


  public function keys( string $key )
  {
    $elem = &$this->findKey( $key );

    if( ! is_array($elem))
      throw new \Exception("$key is no array");

    return array_keys( $elem );
  }

?>

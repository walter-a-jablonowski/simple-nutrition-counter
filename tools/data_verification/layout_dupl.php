<?php

// Filter duplicates from layout

// ...

array_keys( array_filter( array_count_values($done), function($count) {
  return $count > 1;
}))

?>

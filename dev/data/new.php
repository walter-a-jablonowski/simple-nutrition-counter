<?php

require '../../src/lib/frm/Printer_MOV.php';

// https://world.openfoodfacts.org/api/v3/product/4337256080842.json

// AI seems to have problems with this 2411
//
// I need a PHP code that maps try json data in try.json to the blank food format. Have a look at the comments there which tell you how to map and match the data fields as good as possible. Indent all codes with 2 spaces and put the { on the next line.

file_put_contents('out.yml', Printer::run('my.yml', [
  'title' => 'Hello World'
]));

?>

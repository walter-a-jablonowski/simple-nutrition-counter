<?php

if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inputText']))
{
  $s = trim($_POST['inputText']);

  // preg_replace('/\s+/', ' ', $originalString);
  $lines = explode("\n", trim($s));
  $vals['fibre'] = null;  // default (may be missing)

  foreach( $lines as $line )
  {
    $key = null;

    if( stripos( $line, 'kcal') !== false)
      $key = 'calories';
    elseif( stripos( $line, 'fett') !== false && stripos( $line, 'ttigt') === false)
      $key = 'fat'; 
    elseif( stripos( $line, 'ttigt') !== false)
      $key = 'saturatedFat';
    elseif( stripos( $line, 'kohle') !== false && stripos( $line, 'zucker') === false)
      $key = 'carbs';
    elseif( stripos( $line, 'zucker') !== false)
      $key = 'sugar';
    elseif( stripos( $line, 'bala') !== false)
      $key = 'fibre';
    elseif( stripos( $line, 'eiw') !== false)
      $key = 'amino';
    elseif( stripos( $line, 'salz') !== false)
      $key = 'salt';

    if( $key )
      // $vals[$key] = str_replace(',', '.', preg_match("/\d+,\d+/", $line, $m) ? $m[0] : null);
      $vals[$key] = str_replace(',', '.', preg_match("/\d+(?:\,\d+)?/", $line, $m) ? $m[0] : null);
  }

  $output  = "  calories:          $vals[calories]\n";
  $output .= "  nutritionalValues:\n";
  $output .= "\n";
  $output .= "    fat:             $vals[fat]\n";
  $output .= "    saturatedFat:    $vals[saturatedFat]\n";
  $output .= "    carbs:           $vals[carbs]\n";
  $output .= "    sugar:           $vals[sugar]\n";
  $output .= ! $vals['fibre'] ? '' :
             "    fibre:           $vals[fibre]\n";
  $output .= "    amino:           $vals[amino]\n";
  $output .= "    salt:            $vals[salt]\n";

  echo json_encode(['result' => $output]);
  exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Import tool</title>
  <style>
    textarea, div.output { font-family: monospace; }
    div.output { white-space: pre-wrap; }
  </style>
</head>
<body>

  <h3>Import tool</h3>
  <textarea id="inputText" rows="10" cols="50"></textarea><br>
  <button id="parseBtn">Parse</button>
  <br><br>
  <div id="output" class="output"></div>

<script>

  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('parseBtn').addEventListener('click', function() {

      const inputText = document.getElementById('inputText').value

      fetch('', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `inputText=${encodeURIComponent(inputText)}`
      })
      .then(response => response.json())
      .then(data => {
        document.getElementById('output').innerHTML = data.result
      })
      .catch(error => alert('Error: ' + error.message))
    })
  })

</script>
</body>
</html>

<?php

function yml_replace_value( string $yamlContent, string $key, string $newValue) : string
{

  $pattern = '/(?:^|\n)(\s*' . preg_quote($key, '/') . '(\s*:\s*))(["\']?)([^"\'\n]*?)(\3)(\s*)(?=\r?\n|$)/m';

  // 1. Start of line or after newline, followed by optional spaces
  // 2. The exact key
  // 3. Captures any spaces and colon after the key
  // 4. Captures any spaces after the colon
  // 5. Captures any quotes around the current value
  // 6. The current value and any trailing spaces until newline or if quoted until closing quote + spaces
  
  return preg_replace_callback( $pattern, function($matches) use ($newValue) {

    $indentAndKey = $matches[1];    // contains spaces + key + spaces + colon + spaces
    $quote = $matches[3];           // captured quote character (if any)
    $trailingSpaces = $matches[6];  // trailing spaces after the value
    
    // Preserve the original quoting style
    if( $quote )
      // if value was quoted, keep the same quote style
      return "\n" . $indentAndKey . $quote . $newValue . $quote . $trailingSpaces;
    else
      // if value wasn't quoted, don't add quotes
      return "\n" . $indentAndKey . $newValue . $trailingSpaces;
  }, $yamlContent);
}

?>

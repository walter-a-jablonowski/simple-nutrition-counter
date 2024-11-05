<?php

$yaml = <<<YAML
server:
  host: "localhost"
  port: 8080
  debug: true
  name:     "test-server"  
  timeout:      30
  api_key: 'abc123'
YAML;

echo "Original YAML:\n";
echo $yaml . "\n\n";

// Replace a quoted value (preserves double quotes)
$result1 = yml_replace_value($yaml, 'host', '127.0.0.1');
echo "After replacing 'host':\n";
echo $result1 . "\n\n";

// Replace a non-quoted value (remains unquoted)
$result2 = yml_replace_value($result1, 'port', '9090');
echo "After replacing 'port':\n";
echo $result2 . "\n\n";

// Replace a value with spaces in formatting (preserves spaces)
$result3 = yml_replace_value($result2, 'name', 'production-server');
echo "After replacing 'name':\n";
echo $result3 . "\n\n";

// Replace a value with single quotes (preserves single quotes)
$result4 = yml_replace_value($result3, 'api_key', 'xyz789');
echo "After replacing 'api_key':\n";
echo $result4;

function yml_replace_value(string $yamlContent, string $key, string $newValue) : string
{
  // Pattern matches:
  // 1. Start of line or after newline, followed by optional spaces
  // 2. The exact key
  // 3. Captures any spaces and colon after the key
  // 4. Captures any spaces after the colon
  // 5. Captures any quotes around the current value
  // 6. The current value (until newline, or if quoted until closing quote)
  
  $pattern = '/(?:^|\n)(\s*' . preg_quote($key, '/') . '(\s*:\s*))(["\']?)([^"\'\n]*?)(\3)(?=\r?\n|$)/m';
  
  return preg_replace_callback( $pattern, function($matches) use ($newValue) {
    $indentAndKey = $matches[1];  // Contains spaces + key + spaces + colon + spaces
    $quote = $matches[3];         // Captured quote character (if any)
    
    // Preserve the original quoting style
    if( $quote  )
      // If value was quoted, keep the same quote style
      return "\n" . $indentAndKey . $quote . $newValue . $quote;
    else
      // If value wasn't quoted, don't add quotes
      return "\n" . $indentAndKey . $newValue;
  }, $yamlContent);
}

?>

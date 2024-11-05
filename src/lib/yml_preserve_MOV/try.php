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

?>

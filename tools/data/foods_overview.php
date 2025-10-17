<?php

require __DIR__ . '/../../src/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

$file = realpath(__DIR__ . '/../../src/misc/inline_help/foods.yml');
$raw = $file && is_file($file) ? file_get_contents($file) : '';
$data = null;
$error = null;

try {
  if ($file) {
    $data = Yaml::parseFile($file);
  }
}
catch (ParseException $e) {
  $error = $e->getMessage();
}

function normalize_field($key, $value)
{
  $row = [
    'field' => (string)$key,
    'type' => '',
    'required' => '',
    'usage' => '',
    'children' => null,
  ];

  if (is_array($value)) {
    // Group with children (e.g., nutritionalValues)
    $hasDirectMeta = array_key_exists('usage', $value) || array_key_exists('type', $value) || array_key_exists('required', $value);
    $children = [];
    foreach ($value as $k => $v) {
      if (in_array($k, ['usage', 'type', 'required'], true)) {
        continue;
      }
      $children[$k] = $v;
    }

    if ($hasDirectMeta) {
      $row['type'] = isset($value['type']) ? (string)$value['type'] : '';
      $row['required'] = isset($value['required']) ? (is_bool($value['required']) ? ($value['required'] ? 'true' : 'false') : (string)$value['required']) : '';
      $row['usage'] = isset($value['usage']) ? (string)$value['usage'] : '';
    }

    if (!empty($children)) {
      $row['children'] = $children;
    }
  }
  elseif (is_string($value)) {
    // Single usage text
    $row['usage'] = $value;
  }
  elseif ($value === null) {
    // nothing more
  }
  else {
    $row['usage'] = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }

  return $row;
}

function collect_rows($data, $prefix = '')
{
  $rows = [];
  if (!is_array($data)) {
    return $rows;
  }

  foreach ($data as $key => $value) {
    $row = normalize_field($prefix . $key, $value);

    // If children present, add a section row then child rows indented via prefix
    if (is_array($value) && $row['children']) {
      $rows[] = $row; // section header (may also have its own meta)
      foreach ($row['children'] as $ck => $cv) {
        $child = normalize_field($ck, $cv);
        $child['field'] = $row['field'] . ' › ' . $child['field'];
        $rows[] = $child;
      }
    }
    else {
      $rows[] = $row;
    }
  }

  return $rows;
}

$rows = collect_rows($data);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Foods.yml – Field Overview</title>
  <style>
    :root {
      --bg: #0f1116;
      --card: #161a22;
      --muted: #8a94a6;
      --text: #e6e9ef;
      --accent: #5aa6ff;
      --border: #2a3140;
      --req: #ffc24b;
    }
    html, body { margin: 0; padding: 0; background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, sans-serif; }
    .wrap { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
    h1 { font-size: 22px; font-weight: 600; margin: 0 0 16px; }
    .meta { color: var(--muted); font-size: 13px; margin-bottom: 20px; }

    .card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 10px 12px; border-bottom: 1px solid var(--border); vertical-align: top; }
    .table th { text-align: left; font-size: 12px; letter-spacing: .02em; color: var(--muted); font-weight: 600; background: #191e28; }
    .table tr:hover td { background: rgba(90, 166, 255, 0.04); }
    .k { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace; background: #0d1117; border: 1px solid var(--border); padding: 2px 6px; border-radius: 6px; font-size: 12px; }
    .type { color: #9cd2ff; }
    .req { color: var(--req); font-weight: 600; }
    .usage { color: #d6dae3; }
    .warn { background: #2a1f1f; border: 1px solid #5a2b2b; color: #ffb4b4; padding: 10px 12px; border-radius: 8px; margin-bottom: 14px; }
    .file { color: var(--muted); }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Foods – Data Field Overview</h1>
    <div class="meta">
      <span class="file">Source:</span> <code><?= htmlspecialchars($file ?: 'not found') ?></code>
    </div>

    <?php if ($error) : ?>
      <div class="warn">
        YAML parse error. Showing best-effort overview. Details: <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 30%">Field</th>
            <th style="width: 15%">Type</th>
            <th style="width: 10%">Required</th>
            <th>Usage</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!empty($rows)) : ?>
          <?php foreach ($rows as $r) : ?>
            <tr>
              <td><span class="k"><?= htmlspecialchars($r['field']) ?></span></td>
              <td class="type"><?= htmlspecialchars($r['type']) ?></td>
              <td class="req"><?= htmlspecialchars($r['required']) ?></td>
              <td class="usage"><?= htmlspecialchars($r['usage']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <?php
            // Fallback: quick scan of raw lines for keys when parsing fully failed
            $lines = explode("\n", (string)$raw);
            foreach ($lines as $ln) {
              $trim = trim($ln);
              if ($trim === '' || str_starts_with($trim, '#')) { continue; }
              if (preg_match('/^([A-Za-z0-9_().-]+):/', $trim, $m)) {
                $k = $m[1];
          ?>
                <tr>
                  <td><span class="k"><?= htmlspecialchars($k) ?></span></td>
                  <td class="type"></td>
                  <td class="req"></td>
                  <td class="usage"></td>
                </tr>
          <?php
              }
            }
          ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>

<?php

$dir   = 'debug';
$files = [];

function scanDirRecursively($dir)
{
  global $files;

  foreach( scandir($dir) as $fil )
  {
    if($fil == '.' || $fil == '..') continue;
    
    if( is_dir("$dir/$fil"))
    {
      $files[] = ['type' => 'folder', 'name' => $fil, 'path' => "$dir/$fil"];
      scanDirRecursively("$dir/$fil");
    }
    else
      $files[] = ['type' => 'file', 'name' => $fil, 'path' => "$dir/$fil"];
  }
}

scanDirRecursively($dir);

$mergedContent = "Context:\n\n";

if( isset($_POST['merge']) && ! empty($_POST['files']))
{
  foreach( $_POST['files'] as $file )
    $mergedContent .= $file . "\n\n```\n" . file_get_contents($file) . "\n```\n\n";

  file_put_contents('output.txt', $mergedContent);
}

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI merge code</title>
  <!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .arrow {
      display: inline-block;
      width: 10px;
      height: 10px;
      border: solid black;
      border-width: 0 3px 3px 0;
      padding: 3px;
      transform: rotate(45deg);
      transition: transform 0.2s;
    }
    .collapsed .arrow {
      transform: rotate(-45deg);
    }
    .collapsed > ul {
      display: none;
    }
    /* div.output { font-family: monospace;, white-space: pre-wrap; } */
  </style>
</head>
<body class="container mt-5">

  <div class="mb-3">
    <button class="btn btn-sm btn-secondary"   onclick="expandAll()">Expand All</button>
    <button class="btn btn-sm btn-secondary" onclick="collapseAll()">Collapse All</button>
  </div>

  <form method="post">
    <ul>
      <?php $openFolders = 0; ?>
      <?php foreach($files as $item): ?>
        <?php if( $item['type'] == 'folder'): ?>
          <?php if( $openFolders > 0): ?>
            </ul></li>  <!-- Close the previous folder before starting a new one -->
          <?php endif; ?>
          <li class="collapsed">
            <div class="folder" onclick="toggleNode(this)">
              <span class="arrow"></span> <?= $item['name'] ?>
            </div>
            <ul>
        <?php $openFolders++; ?>
        <?php else: ?>
          <li>
            <input type="checkbox" name="files[]" value="<?= $item['path'] ?>"> <?= $item['name'] ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php while( $openFolders > 0 ):  // close any remaining open folders ?>
        </ul></li>
      <?php $openFolders--; endwhile; ?>
    </ul>
    <button type="submit" name="merge" class="btn btn-sm btn-secondary mt-3">Merge</button>
    <span style="color: grey;">click for copy (or use output.txt)</span>
  </form>

  <?php if( ! empty( $mergedContent )): ?>
    <h3 class="mt-5">Merged Output</h3>
    <pre id="output" class="p-3 border bg-light"><?= htmlspecialchars($mergedContent) ?></pre>
  <?php endif; ?>

<script>
  function toggleNode(element) {
    element.parentNode.classList.toggle('collapsed');
  }
  function expandAll() {
    const folders = document.querySelectorAll('.folder');
    folders.forEach(folder => folder.parentNode.classList.remove('collapsed'));
  }
  function collapseAll() {
    const folders = document.querySelectorAll('.folder');
    folders.forEach(folder => folder.parentNode.classList.add('collapsed'));
  }

  // Copy clipboard

  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('output').addEventListener('click', function() {
      const text = this.innerText
      navigator.clipboard.writeText(text)
    })
  })
</script>
</body>
</html>

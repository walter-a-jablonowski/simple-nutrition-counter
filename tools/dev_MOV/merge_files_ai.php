<?php

// I am making a PHP tool that can list all files of an app, and then print a selection of them in a file and on screen. This is used to merge selected files as context input for AIs.
//
// Use scandir() to list all files and folders recursively. Print as html tree with foldable nodes. Use an arrow in the form of a triangle in front of each folder that acts as folding control. Also add a checkbox before each file name for selection. Initially all nodes are collapsed. Add buttons for collapse all and expand all.
//
// When a merge button is pressed, all selected files are printed as one like
//
//   my/folder/file.ext
//
//   ```
//   ... file content goes here ...
//   ```
//
//   more files ...
//
// Print the output on screen and in a file named output.txt
//
// Make the tool as a single PHP file and indent all code with 2 spaces. Prefer putting the PHP code before the html code as much as possible. Make no functions where you don't need them. Use bootstrap for styling. Use no echo for printing html, but plain html and PHP's alternative syntax for if, foreach, ...

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
    ul {
      list-style-type: none;
      margin-left: 0;
    }
    .arrow {
      display: inline-block;
      width: 0;
      height: 0;
      border-left: 6px solid transparent;
      border-right: 6px solid transparent;
      border-top: 10px solid black;
      margin-right: 5px;
      transform: rotate(0deg);
      transition: transform 0.2s;
    }
    .collapsed .arrow {
      transform: rotate(30deg);
    }

    .collapsed > ul {
      display: none;
    }
  </style>
</head>
<body class="container-fluid mt-2">

  <div class="mb-3">
    <button class="btn btn-sm btn-secondary" onclick="expandAll()">Expand all</button>
    <button class="btn btn-sm btn-secondary" onclick="collapseAll()">Collapse all</button>
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
    <pre id="output" class="mt-3 p-2 border bg-light"><?= htmlspecialchars($mergedContent) ?></pre>
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

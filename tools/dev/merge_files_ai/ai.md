I am making a PHP tool that can list all files of an app, and then print a selection of them in a file and on screen. This is used to merge selected files as context input for AIs.

Use scandir() to list all files and folders recursively. Print as html tree with foldable nodes. Use an arrow in the form of a reatangle in front of each folder that acts as folding control. Also add a checkbox before each file name for selection. Initially all nodes are collapsed. Add buttons for collapse all and expand all.

When a merge button is pressed, all selected files are printed as one like

  my/folder/file.ext

  ```
  ... file content goes here ...
  ```

  more files ...

Print the output on screen and in a file named output.txt

Make the tool as a single PHP file and indent all code with 2 spaces. Prefer putting the PHP code before the html code as much as possible. Make no functions where you don't need them. Use bootstrap for styling. Use no echo for printing html, but plain html and PHP's alternative syntax for if, foreach, ...

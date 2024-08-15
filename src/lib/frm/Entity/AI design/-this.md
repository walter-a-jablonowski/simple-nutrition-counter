```
I am developing a class library in PHP that represents data objects save in the
file system as a tree of objects. Each object may be a yml file or a folder. If it is a
folder the primary object data is in FOLDER/-this.yml and the object may have more data
files. It also may have may have sub objects. The names of the object files or folders are
the id of the object. There also may be folders ony any level of the tree that are used
for grouping larger lists of files and folders. An object may be linked to a second object
somewhere in the tree by adding a file that represents the link.

Please design classes that allow me to load and save objects and their data,
navigate the tree, follow links. Make the classes and method definition only, so that
I can see how it would work, no implementation for now. Also make some sample code.
```

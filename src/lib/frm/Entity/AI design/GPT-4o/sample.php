<?php

// Initialize the file system with a root folder
$fs = new FileSystem('/path/to/root');

// Load the root folder's data
$root = $fs->getRoot();
$root->loadData();

// Add a new object (folder) to the root
$newFolder = new Folder('new_folder', '/path/to/root/new_folder');
$root->addChild($newFolder);

// Load data into the new folder
$newFolder->loadData();

// Add a YAML file to the new folder
$yamlFile = new YamlFile('data', '/path/to/root/new_folder/data.yml');
$newFolder->addChild($yamlFile);
$yamlFile->loadData();

// Save the data back to the file system
$newFolder->saveData();

// Create a link to the new folder from another part of the tree
$link = new Link('link_to_new_folder', '/path/to/root/some_other_folder/link', $newFolder);
$root->addChild($link);

// Follow the link to access the target folder
$linkedFolder = $fs->followLink($link);
$linkedFolder->loadData();

// Get primary data from the linked folder
$primaryData = $linkedFolder->getPrimaryData()->getData();

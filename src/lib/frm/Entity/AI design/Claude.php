<?php

// Sample usage
$fsManager = new FileSystemManager('/path/to/root');
$rootObject = $fsManager->loadObject('root');
$navigator = new ObjectNavigator($rootObject);

// Load an object
$object = $fsManager->loadObject('some/nested/object');

// Modify data
$object->setData(['key' => 'value']);

// Add a child
$childObject = new DataObject('child', $object);
$object->addChild($childObject);

// Save changes
$fsManager->saveObject($object);

// Navigate
$foundObject = $navigator->findObjectById('some/nested/object/child');
$path = $navigator->getPath($foundObject);

// Traverse the tree
$navigator->traverseTree(function($object) {
    echo $object->getId() . "\n";
});

// Handle links
$linkedObject = $fsManager->loadObject('another/object');
$object->addLink($linkedObject);
$linkedObjects = $navigator->getLinkedObjects($object);


class DataObject {
    protected $id;
    protected $data;
    protected $parent;
    protected $children;
    protected $links;

    public function __construct($id, $parent = null) {}
    public function getId(): string {}
    public function getData(): array {}
    public function setData(array $data): void {}
    public function getParent(): ?DataObject {}
    public function getChildren(): array {}
    public function addChild(DataObject $child): void {}
    public function removeChild(DataObject $child): void {}
    public function getLinks(): array {}
    public function addLink(DataObject $linkedObject): void {}
    public function removeLink(DataObject $linkedObject): void {}
    public function save(): void {}
    public function delete(): void {}
}

class FileSystemManager {
    protected $rootPath;

    public function __construct(string $rootPath) {}
    public function loadObject(string $id): DataObject {}
    public function saveObject(DataObject $object): void {}
    public function deleteObject(DataObject $object): void {}
    public function getObjectPath(string $id): string {}
    protected function isFolder(string $path): bool {}
    protected function loadYamlFile(string $path): array {}
    protected function saveYamlFile(string $path, array $data): void {}
}

class ObjectNavigator {
    protected $rootObject;

    public function __construct(DataObject $rootObject) {}
    public function findObjectById(string $id): ?DataObject {}
    public function getPath(DataObject $object): array {}
    public function traverseTree(callable $callback): void {}
    public function getLinkedObjects(DataObject $object): array {}
}

<?php

abstract class FileSystemObject {
    protected string $id;
    protected string $path;

    public function __construct(string $id, string $path) {
        $this->id = $id;
        $this->path = $path;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getPath(): string {
        return $this->path;
    }

    abstract public function loadData(): void;
    abstract public function saveData(): void;
}

class YamlFile extends FileSystemObject {
    private array $data;

    public function loadData(): void {
        // Load YML data from the file
    }

    public function saveData(): void {
        // Save YML data to the file
    }

    public function getData(): array {
        return $this->data;
    }

    public function setData(array $data): void {
        $this->data = $data;
    }
}

class Folder extends FileSystemObject {
    private array $children = [];
    private ?YamlFile $primaryData = null;

    public function __construct(string $id, string $path) {
        parent::__construct($id, $path);
        $this->primaryData = new YamlFile('-this', $this->path . '/-this.yml');
    }

    public function loadData(): void {
        $this->primaryData->loadData();
        // Load child objects (files and folders)
    }

    public function saveData(): void {
        $this->primaryData->saveData();
        // Save child objects (files and folders)
    }

    public function getPrimaryData(): YamlFile {
        return $this->primaryData;
    }

    public function addChild(FileSystemObject $child): void {
        $this->children[$child->getId()] = $child;
    }

    public function getChild(string $id): ?FileSystemObject {
        return $this->children[$id] ?? null;
    }

    public function getChildren(): array {
        return $this->children;
    }
}

class Link extends FileSystemObject {
    private FileSystemObject $target;

    public function __construct(string $id, string $path, FileSystemObject $target) {
        parent::__construct($id, $path);
        $this->target = $target;
    }

    public function loadData(): void {
        // Load link data, if any
    }

    public function saveData(): void {
        // Save link data, if any
    }

    public function getTarget(): FileSystemObject {
        return $this->target;
    }
}

class FileSystem {
    private Folder $root;

    public function __construct(string $rootPath) {
        $this->root = new Folder('root', $rootPath);
    }

    public function getRoot(): Folder {
        return $this->root;
    }

    public function findObjectByPath(string $path): ?FileSystemObject {
        // Implement a method to locate and return an object by its path
    }

    public function followLink(Link $link): FileSystemObject {
        return $link->getTarget();
    }
}

# Deploy tool

A small PHP script to update an installed copy of an app by copying the current `src/` folder into a destination folder.

## What it does

- Creates a timestamped backup of selected paths from destination (`DEPLOY_BACKUP`),
  as a folder or as a single zip archive (`DEPLOY_BACKUP_ZIP`)
- Deletes everything in destination that is no in `DEPLOY_KEEP`
- Copies everything from source to destination that is no in `DEPLOY_KEEP`
- Always ignores `desktop.ini` (never delete/copy/backup)
- Won't delete folders that have a desktop.ini (some Win error)

## Usage

- We may need to stop a tool before deploying, so that no files are locked
- Each run: we might to have to copy data (keep) manually

1. Open `config.php` and set:

all optional except source and dest, dest and backup folders are created if missing

- `DEPLOY_SOURCE_DIR` (usually `../src`)
- `DEPLOY_DEST_DIR` (your installation folder)
- `DEPLOY_BACKUP_DIR` (where backups are stored)
- `DEPLOY_IGNORE` (ignore from source folder)
- `DEPLOY_BACKUP` (what to backup before deploying)
- `DEPLOY_BACKUP_ZIP` (false: backup folder, true: single zip archive)
- `DEPLOY_PYTHON` (command that runs python, only used for the zip)
- `DEPLOY_KEEP` (what must survive cleanup/deploy)

2. Run:

```bashw
php deploy.php
```

## Comments

- Run `deploy.php` from its own folder: `DEPLOY_SOURCE_DIR` is relative to the
  working directory, so `../../src` only resolves from here
- Source and destination are checked before anything is deleted (the destination
  is cleared before it is filled, so a wrong path would empty the installation)
- Backup folders are created as `DEPLOY_BACKUP_DIR/YYMMDD_HHMMSS`, archives as
  `DEPLOY_BACKUP_DIR/YYMMDD_HHMMSS.zip` (same paths inside, empty folders kept)
- The zip is made by `zip_helper.py`, so `DEPLOY_BACKUP_ZIP = true` needs python.
  `lib.php` hands it the item list as a temporary json manifest and deletes that
  file again; a failing zip aborts the deploy before anything is deleted
- `make_debug_data.php` creates a `debug/` folder structure for local testing

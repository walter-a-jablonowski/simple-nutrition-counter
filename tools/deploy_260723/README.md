# Deploy tool

A small PHP script to update an installed copy of an app by copying the current `src/` folder into a destination folder.

## What it does

- Creates a timestamped backup of selected paths from destination (`DEPLOY_BACKUP`)
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
- `DEPLOY_IGNORE` (ignore drom source folder)
- `DEPLOY_BACKUP` (what to backup before deploying)
- `DEPLOY_KEEP` (what must survive cleanup/deploy)

2. Run:

```bashw
php deploy.php
```

## Comments

- Backup folders are created as `DEPLOY_BACKUP_DIR/YYMMDD_HHMM`
- `make_debug_data.php` creates a `debug/` folder structure for local testing

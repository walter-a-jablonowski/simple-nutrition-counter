# Publish foods

Copies the food data to the installation folder (see `destination` in `config.yml`),
which has the same structure as this repo.

Reachable two ways:

- App: dev menu (gear, devMode only) in the day toolbar > **Publish**
- CLI, run from `src`:

```
php tools/publish_foods/publish.php                   # dry run, shows what would change
php tools/publish_foods/publish.php --run             # copy new + changed files
php tools/publish_foods/publish.php --run --delete    # also remove obsolete files
```


## Why hashes and not file dates

The destination is a Google Drive folder. Drive rewrites timestamps while syncing,
so a file time there says nothing about the content. File times are also wrong in
both directions in general: a re-save or a `git checkout` bumps the time without
changing anything, and two different versions can share a time.

Every file is therefore compared by SHA-1. The whole data set is a few MB, so a
full re-hash costs milliseconds.

`published.json` (git ignored) records the hash of every file of the last publish.
That lets a normal run decide everything from the source side, without reading the
Google Drive folder at all. Only when a path is unknown to the manifest does the
tool hash the destination file - which is what keeps a first run from re-copying
everything.


## Backup

Publishing overwrites files at the destination, so the version that is about to
be replaced is copied to the `backup` folder first:

```
simple-nutrition-counter_backup/2026-07-23_01/src/data/bundles/.../foods/Apfel.yml
```

One folder per run, `YYYY-MM-DD_NN` with the counter starting at `01` each day,
and the file's sub path kept inside it. Only files that really get replaced or
deleted are saved - a new file has no older version to lose, and an unchanged
file is not touched at all. The folder is created only when there is something
to put in it.

If the backup folder cannot be written (Drive offline, for instance) the run
stops before copying or deleting anything: publishing without the safety net is
worse than not publishing.


## Report

```
NEW  src/data/bundles/.../foods/Apfel.yml     file is not at the destination yet
CHG  src/data/bundles/.../layout.yml          content differs
OBS  src/data/bundles/.../foods/Alt.yml       published before, gone from the sources
DEL  ...                                      same, and it is being deleted
```

Obsolete files are only removed when deletion is asked for explicitly (checkbox in
the dialog, `--delete` on the CLI). Otherwise they stay and keep being reported.

`desktop.ini`, `Thumbs.db` and `.DS_Store` are never copied and never reported -
Windows and Drive create those on their own (see `ignore` in `config.yml`).


## Extending

Add paths to `sources` in `config.yml`, relative to the repo root. Files and
folders both work, folders are walked recursively.

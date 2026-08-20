import json
import os
import sys
import zipfile


def backup_to_zip(items, zip_path):
  """
  Packs several files/directories into a single zip file.

  Args:
    items:    List of dicts with 'source' (file or directory) and 'arc'
              (the path the item gets inside the archive).
    zip_path: Path of the zip file to create.
  """
  with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
    for item in items:
      add_item(zipf, item['source'], item['arc'])


def add_item(zipf, source, arc):
  """Adds one file or one directory (recursively) to an open archive."""

  if os.path.isfile(source):
    zipf.write(source, arc_name(arc))

  elif os.path.isdir(source):
    for root, dirs, files in os.walk(source):
      names = [f for f in files if f.lower() != 'desktop.ini']
      sub   = os.path.relpath(root, source)
      base  = arc if sub == '.' else os.path.join(arc, sub)

      if not names and not dirs:                     # keep empty folders
        zipf.writestr(f"{arc_name(base)}/", '')
        continue

      for name in names:
        zipf.write(os.path.join(root, name), arc_name(os.path.join(base, name)))


def arc_name(path):
  """Zip entries always use forward slashes."""
  return path.replace('\\', '/').strip('/')


if __name__ == "__main__":

  if len(sys.argv) != 2:
    print("Usage: python zip_helper.py <manifest.json>")
    sys.exit(1)

  with open(sys.argv[1], encoding='utf-8') as fh:
    manifest = json.load(fh)

  items = manifest['items']

  for item in items:
    if not os.path.exists(item['source']):
      print(f"Source not found: {item['source']}")
      sys.exit(1)

  backup_to_zip(items, manifest['zip'])
  print(f"Zip created: {manifest['zip']}")

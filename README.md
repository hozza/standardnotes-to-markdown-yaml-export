# Export Standard Notes to Markdown

This script can be used to export "Standard Notes" notes into plaintext markdown files while retaining the notes metadata like modification time, creation time, notes status (archived, trashed, pinned) and any tags.

Key Features:
- Export Standard Notes to Markdown
- Keeps modification date of notes
- Keeps tags
- Universal format (e.g. great for Zettlr, Obsidian.md and Notable.app or plain-text)
- Keeps/creates Zettelkasten features
- Removed duplicates from corrupted Standard Notes exports
- Can be used as a ongoing longevity backup

## Installation & Usage

On Linux install php, download the script and from terminal.

In the same directory as the Standard Notes exported backup file:

```
php ./standard-notes-to-markdown.php name-of-sn-export-file.txt ./location/to/export/notes/
```

*Notice: I wrote this one afternoon after realizing the Standard Notes exported backup markdown files don't contain any tags or modification times etc. It's written quickly in PHP with no particular coding standard.*


## Additional Info and Examples

It will take all the information from Standard Notes, remove duplicate notes and tags and outputs separate markdown files for each note with all the tags and dates etc. This is also useful for fixing Standard Notes duplication/corruption bugs during export.

Notes are exported into the path you specify or if no path is specified `./notes/`, with the filename of `Note Title ID_NUMBER.md` with the appropriate modification time on the file. 

*Any title character commonly not allowed in filenames will be replaced with a hyphen `-`.*

**YAML** is extra information inside a text file, that contains structured information in an easily readable format for humans. A fair amount of note apps use YAML to keep notes universal, however your note app does not use YAML, it's still an unobtrusive way of storying information like titles, tags, IDs and creation date.


### Migrating/Exporting [Standard Notes](https://github.com/standardnotes/web) to [Zettlr](https://github.com/zettlr/zettlr), [Notable.app](https://github.com/notable/notable), [Obsidian.md](https://obsidian.md/) and other Second Brain/[PKMs](https://en.wikipedia.org/wiki/Personal_knowledge_management).

In 2020-02 you can export a backup of Standard Notes from the app, `Account -> Data Backups`, and choose option "Decrypted", to download the zip file.

This Standard Notes backup zip file contains a `Standard Notes Backup and Import File txt.txt` and a folder with individual markdown files **but** without tags or metadata.

The `Standard Notes Backup and Import File txt.txt` is actually a JSON file containing all the notes content and metadata like tags, dates etc. We use this file to recreate markdown files with all the wanted info in a more portable/universal format.

Once you've exported all SN notes into Markdown with YAML, you can use these natively in a basic text editor or import into other note apps, personal knowledge management systems.

#### Zettelkasten Method Features

*If you don't use Zettelkasten methods for notes, you can still use this exporter without worry.*

Zettelkasten is a common method of managing notes in many PKM/PKB systems, it's great for universal note formats and doesn't tie you to any one app or propriety storage system, promoting real longevity and universality of your notes.

A common approach is to link notes together in markdown with wiki style links `[[link]]` and naming notes with an Unique ID number so that links between notes don't break when the app/system changes or the notes are renamed. Zettel IDs are commonly a timestamp in the format similar to ISO 8601 e.g. `20210218112452`.

This exporter script created IDs based on the note creation time, if this is not unique (due to automatic historic importing for example) it'll increment seconds until it is unique. 

The notes Zettel UID is added as YAML, as well as Standard Notes UUID for good measure.


### Standard Notes Universal Plain-text Longevity Backup

You can use this as a regular running script on a Linux system via `cron` to convert the basic Standard Notes export files into a more longevity focused and robust format while keeping key meta information like tags, and times/dates available.

Have Standard Notes "CloudLink" email or save regular backups/exports into the cloud *decrypted* then have this script convert the exported JSON file on a regular basis.

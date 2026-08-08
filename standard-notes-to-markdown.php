<?php

/* 

# Export [Standard Notes](https://github.com/standardnotes/web) decrypted backup JSON as separate Markdown files with YAML frontmatter. 

Link: https://github.com/hozza/standardnotes-to-markdown-yaml-export

Great keeping your notes metadata in a plaintext backup for longevity, or for migrating to [Zettlr](https://github.com/zettlr/zettlr), [Notable.app](https://github.com/notable/notable), and other note taking apps which use a plaintext markdown format.


Run with `php standard-notes-to-markdown.php sn-export-filename.txt ./export/directory/`

***

## Standard Notes JSON Backup Export Decrypted Syntax Specification Raw

There are many "items" in the Standard Notes exported JSON, but we're only concerned with Notes and Tags here. 

2021-02 Standard Notes exports markdown files in a sub-directory zip along with the file `Standard Notes Backup and Import File txt.txt`, these markdown files do not include meta data, but the JSON file includes metadata and notes content, so we just need this JSON file passed as argument 1.

Made for Standard Notes export JSON file which indicated `"version": "004"`, it may work for other versions.


### Notes Item

The Standard Notes "Note item" type can also have a bool of 'content>pinned' and a 'content>archived' instead of 'content>trashed' as shown in example.

```
    {
      "uuid": "1fa7d986-e8f7-4121-bcf2-7f408c85ed03",
      "content_type": "Note",
      "content": {
        "title": "personal-todo",
        "text": "## Title\n\n## Todo\n\n## Title1\n\n## Title2\n\n## Title5\n\n## Remember\n\nUnformatted Notes content/ to organize\n===============================\n\n",
        "references": [],
        "appData": {
          "org.standardnotes.sn": {
            "client_updated_at": "2014-05-30T15:39:59.000Z"
          }
        },
        "trashed": true
      },
      "created_at": "2014-05-30T15:39:59.000Z",
      "updated_at": "2021-02-20T18:20:24.915Z",
      "duplicate_of": null
    },
```

### Tag Item

Standard Note Tag titles contain a dot `.` if a paid account (called "Extended") had the ["Folders" or "Smart Tag"](https://standardnotes.org/extensions/folders) extension enabled and had nested tags/folders, the dot separates the parent tag from child tag. 

```
    {
      "uuid": "321a849e-d854-4cc4-808d-6aa0b80bceb8",
      "content_type": "Tag",
      "content": {
        "title": "personal.prose",
        "references": [
          {
            "uuid": "7b938444-5af3-444e-a0b5-e29ef43f6de2",
            "content_type": "Note"
          },
          {
            "uuid": "402e0be4-4ca6-4cd3-8c1a-f9175d82a5d5",
            "content_type": "Note"
          },
          {
            "uuid": "dff98b87-2246-4982-9218-a5224894e4a3",
            "content_type": "Note"
          }
        ],
        "appData": {
          "org.standardnotes.sn": {
            "client_updated_at": "2021-02-18T11:24:52.636Z"
          },
          "org.standardnotes.sn.components": {
            "84887fa7-85dd-41b6-b7c3-97d549460158": {}
          }
        }
      },
      "created_at": "2020-08-26T16:13:11.669Z",
      "updated_at": "2021-02-20T18:20:17.571Z",
      "duplicate_of": null
    },
```


*/

$EXPECTED_CALL_SYNTAX = 'expected format: php standard-notes-to-markdown.php <path-to-backup.txt> [<foldername-tooutput-notes>] [<path-to-folder-you-created-with-attachments>]
example: php standard-notes-to-markdown.php "/Users/foo/Downloads/Standard Notes Backup - Tue Jul 09 2024 11_28_53 GMT+0100/Standard Notes Backup and Import File.txt" /notes/ "/Users/foo/Downloads/standard-notes-attachments/"
';

// Require Args
if(!isset($argv[1])) {
	echo 'Error: Need to pass the Standard Notes file path as argument/parameter 1\n';
	echo $EXPECTED_CALL_SYNTAX;
	exit;
}
else $sn_file = file_get_contents($argv[1]);

if(!isset($argv[2])) $export_path = __DIR__.'/notes/';
else $export_path = __DIR__.trim($argv[2]);

/**
 * (optional) third argument to be a folder containing standard notes files in the format of <uuid>.<ext>
 * <br/>
 * examples: 5e97c50b-ea24-4b52-bd05-b902594b367a.png, 9a09b759-6ab7-4431-b7b3-6096c4635fb8.pdf
 */
if(!isset($argv[3])) $resourceFilesDir = false;
else $resourceFilesDir = $argv[3];

$sn_file_metadata_dir = dirname($argv[1])."/Items/SN_File";
$resource_path = $export_path.'/resources';

// sanity
if(file_exists($export_path)) {
	echo 'Error: Export path already exists! We don\'t want to overwrite anything... Delete it or choose another path.';
	exit;
}
// sanity
if($resourceFilesDir && !file_exists($resourceFilesDir)) {
	echo 'Error: ResourceFileDir is set, but folder does not exist.\n';
	echo $EXPECTED_CALL_SYNTAX;
	exit;
}
else {
	mkdir($export_path);
	mkdir($resource_path);
}


$notes = array();

// load SN file as associative array
$sn_json = json_decode($sn_file, true, 512, JSON_THROW_ON_ERROR);

foreach($sn_json['items'] as $sn_item) {

	// Process the Notes
	if($sn_item['content_type'] == 'Note') {

		// can only really be one or the other as they're locations, right? We'll handle "pinned" later
		if(@$sn_item['content']['appData']['org.standardnotes.sn']['trashed'] == true) {
			$sn_note_status = 'trashed';
		}
		elseif(@$sn_item['content']['appData']['org.standardnotes.sn']['archived'] == true) {
			$sn_note_status = 'archived';
		}
		else $sn_note_status = false;

		// 🖖 Beam me up, Miles O'Brien
		if($sn_note_status) $notes[$sn_item['uuid']]['status'] = $sn_note_status;
		$notes[$sn_item['uuid']]['title'] = $sn_item['content']['title'];
		$notes[$sn_item['uuid']]['sn_content'] = $sn_item['content']['text'];
		$notes[$sn_item['uuid']]['sn_noteType'] = $sn_item['content']['noteType'];
		$notes[$sn_item['uuid']]['created_at'] = $sn_item['created_at'];
		$notes[$sn_item['uuid']]['updated_at'] = $sn_item['content']['appData']['org.standardnotes.sn']['client_updated_at']; // apparently $sn_item['updated_at'] is not what we wanted


		// pinned? Treat as an attribute not location/status.
		if(@$sn_item['content']['appData']['org.standardnotes.sn']['pinned'] == true) $notes[$sn_item['uuid']]['tags']['pinned'] = true;

	}

	// Process the Tags
	if($sn_item['content_type'] == 'Tag') {

		if(count($sn_item['content']['references']) > 0) {
			$JSON[] = $sn_item;

			// loop all notes in tag
			foreach($sn_item['content']['references'] as $tag_refs) {

				// is this tag referencing a note? not sure if it could ever reference anything else
				if($tag_refs['content_type'] == 'Note') {

					// store tag as key in note to prevent duplicates
					if(isset($tag_refs['uuid'])) { // some tags are empty
						$notes[$tag_refs['uuid']]['tags'][$sn_item['content']['title']] = true;
					}
				}

			}

		}

	}

}


// Export Markdown files with YAML frontmatter.
$exported_count = 0;
$note_ids = array();
/**
 * @param $note_data
 * @return string
 * @throws JsonException
 */
function parseContent($note_data, $filename)
{
	$type = $note_data['sn_noteType'];
	$title = $note_data['title'];
	switch($type) {
		case "super":
			$sn_json = json_decode($note_data['sn_content'], true, 512, JSON_THROW_ON_ERROR);
			$lines = array();
			foreach($sn_json['root']['children'] as $child) {
				$lines[] = parseChild($child, $filename);
			}
			return join("\n\n", $lines);
		default:
			echo "could not parse (type=$type) (title=$title), treating it as plain-text\n";
			return $note_data['sn_content'];
		case "markdown":
		case "plain-text":
			return $note_data['sn_content'];
	}
}

/**
 * @throws JsonException
 */
function parseChild($child, $note_filename)
{
	$type = $child['type'];
	switch($type) {
		case "code-highlight":
			// todo: is there value in parsing this further?
			return $child['text'];
		case "hashtag":
			return $child['text'];
		case "text":
			$format = $child['format'];
			list($format_prefix, $metadata_path) = findFormatWrappers($format);
			return $format_prefix.$child['text'].$metadata_path;
		case "table":
			// using reduce because we need to add the line after the first row
			// |first|row|items|
			// |-|-|-|
			// |second|row|items| (optional)
			return array_reduce($child['children'],
				function ($carry, $item) use ($note_filename) {
					if($carry == "") {
						$carry .= parseChild($item, $note_filename);

						// first row needs a border beneath it to be recognized as a table
						$carry .= "\n".
							"|".
							join("|",
								array_map(function ($c) {
									return "-";
								}, $item['children'])
							)
							."|";
					}
					else {
						$carry .= $carry .= parseChild($item, $note_filename);
					}
					$carry .= "\n";
					return $carry;
				}, "");
		case "tablecell":
			return joinChildren("", $child['children'], $note_filename);
		case "collapsible-container":
			return joinChildren("", $child['children'], $note_filename);
		case "collapsible-title":
			return joinChildren("", $child['children'], $note_filename);
		case "paragraph":
			$indent = $child['indent'];
			$paragraphSeparator = "";
			if($indent && $indent > 0) {
//				$paragraphSeparator .= str_repeat("$\quad$", $indent); // hack to make it look tab-like
				$paragraphSeparator .= str_repeat("    ", $indent); // convert into quote
			}
			// todo: should I replace every newline with `$indent * \t + \n`
			return $paragraphSeparator.joinChildren($paragraphSeparator, $child['children'], $note_filename);
		case "tablerow":
			$row = joinChildren("|", $child['children'], $note_filename);
			return "|$row|";

		case "linebreak":
			return "\n";
		case "heading":
			$tag = $child['tag'];
			switch($tag) {
				case "h1":
					$prefix = "# ";
					break;
				case "h2":
					$prefix = "## ";
					break;
				case "h3":
					$prefix = "### ";
					break;
				case "h4":
					$prefix = "#### ";
					break;
				case "h5":
					$prefix = "##### ";
					break;
				case "h6":
					$prefix = "###### ";
					break;
				default:
					$prefix = "#error: header-tag=$tag ";
			}
			return $prefix.joinChildren("", $child['children'], $note_filename);
		case "horizontalrule":
			return "\n---\n";
		case "link":
		case "autolink":
			$url = $child['url'];
			$text = joinChildren("", $child['children'], $note_filename);
			return "[$text]($url)";
		case "snfile":
			$fileUuid = $child['fileUuid'];
			list($original_filename, $metadata_filepath) = lookupResourceFilename($fileUuid);
			global $resource_path, $resourceFilesDir;
			mkdir("$resource_path/$note_filename");
			$extension = preg_replace("/.*\./", "", $original_filename);
			if(file_exists($resourceFilesDir."/$fileUuid.$extension")) {
				copy($resourceFilesDir."/$fileUuid.$extension", "$resource_path/$note_filename/$fileUuid.$extension");
			}
			else {
				echo "error: could not find file uuid=$fileUuid filename='$original_filename' from note $note_filename. This is normal if you did not set argv[3]='$resourceFilesDir'\n";
			}
			copy($metadata_filepath, "$resource_path/$note_filename/metadata_$fileUuid.json"); // name starts with metadata, so ppl don't think their file is there and delete their standard notes account
			return "![[./resources/$note_filename/$fileUuid.$extension]]";
		case "listitem":
			// listitem outside of a list (inside a list, see case "list")
			// should only be in case of web-clipping or such, not normal for standard-notes super note
			$indent = $child['indent'];
			if(!$indent || $indent <= 0) {
				$indent = 0;
			}
			return str_repeat("\t", $indent)."- ".joinChildren("", $child['children'], $note_filename);
		case "list":
			return parseList($child, $note_filename);
		case "code":
			if(!$child['children']) {
				return "\n#error: code without children\n";
			}
			if(firstChildIsType($child, "code")) {
				if(!hasSingleChild($child)) {
					// confirm only 1 child -> lazyness assuming this case never occurs
					// if it does occur, you will want to co something more complicated than getting [0]'t child and ignoring the rest
					return "\n#error: nested code with multiple children\n";
				}
				// don't do anything the nested code will do all that needs to happen
				return parseChild($child['children'][0], $note_filename);
			}

			return "\n```\n".
				joinChildren("", $child['children'], $note_filename)
				."\n```\n";
		case "unencrypted-image":
			$alt = $child['alt'];
			$src = $child['src'];
			return "![{$alt}]({$src})";
		case "quote":
			return "> ".joinChildren("", $child['children'], $note_filename);
		case "tab":
			return "\t";
		default:
			return "#error: unknown type='$type' ";
	}
}

/**
 * @param $list
 * @param $note_filename
 * @return string
 * @throws JsonException
 */
function parseList($list, $note_filename): string
{
	// handle different types of lists (ordered, unordered, checklist)
	$listType = $list['listType'];
	$listTypePrefix = "";
	switch($listType) {
		case "number":
			$listTypePrefix = "1. ";
			break;
		case "bullet":
			$listTypePrefix = "- ";
			break;
		case "check":
			$listTypePrefix = "- [] ";
			break;
		default:
			$listTypePrefix = "#error: unknown listType '$listType' ";
	}

	$parsed = "";
	foreach($list['children'] as $child) {
		$childType = $child['type'];
		if($childType != "listitem") {
			$parsed .= "#error: expected type='listitem' but got type='$childType'";
			continue;
		}

		// handle weird nesting of lists when indented
		if(firstOnlyChildHasType($child, "list")) {
			$parsed .= parseList($child['children'][0], $note_filename);
			continue;
		}

		// handle indentation
		$indent = $child['indent'];
		if(!$indent || $indent <= 0) {
			$indent = 0;
		}
		$indentPrefix = str_repeat("\t", $indent);

		$textInListItem = joinChildren("", $child['children'], $note_filename);
		$obsidianDoesMultilineAlignmentWith3Spaces = "  ";
		$textInListItem = preg_replace("/\n/", "\n"
			.$indentPrefix
			.$obsidianDoesMultilineAlignmentWith3Spaces
			, $textInListItem);
		$parsed .= "\n"
			.$indentPrefix
			.$listTypePrefix
			.$textInListItem;
	}

	return $parsed;
}

/**
 * @param $parent
 * @param $type
 * @return bool
 */
function firstOnlyChildHasType($parent, $type): bool
{
	return hasSingleChild($parent) && firstChildIsType($parent, $type);
}

/**
 * @param $parent
 * @param $type
 * @return bool
 */
function firstChildIsType($parent, $type): bool
{
	return $parent['children'][0]['type'] == $type;
}

/**
 * @param $parent
 * @return bool
 */
function hasSingleChild($parent): bool
{
	return count($parent['children']) == 1;
}

/**
 * @param $fileUuid1
 * @return string[] array(original_filename: string, metadata_filepath: string)
 * @throws JsonException
 */
function lookupResourceFilename($fileUuid)
{
	$fileId = preg_replace("/-.*/", "", $fileUuid);
	global $sn_file_metadata_dir;
	$metadata_filepath = $sn_file_metadata_dir."/SN_File-$fileId.txt";
	$metadata_raw = file_get_contents($metadata_filepath);
	$metadata = json_decode($metadata_raw, true, 512, JSON_THROW_ON_ERROR);
	return array($metadata['name'], $metadata_filepath);
}

/**
 * @param $separator
 * @param $children
 * @param $note_filename
 * @return string
 * @throws JsonException
 */
function joinChildren($separator, $children, $note_filename)
{
	return join($separator,
		array_map(function ($c) use ($note_filename) {
			return parseChild($c, $note_filename);
		}, $children));
}

/**
 * @param $format
 *
 * <br/> looks like binary collapse:
 * <br/> examples
 * <br/> 1 2 4 8 16 32
 * <br/> 0 0 0 0  0  0 = 0 nothing
 * <br/> 1 0 0 0  0  0 = 1 bold
 * <br/> 0 1 0 0  0  0 = 2 italic
 * <br/> 0 0 1 0  0  0 = 4 strikethrough
 * <br/> 0 0 0 1  0  0 = 8 underline
 * <br/> 0 0 0 0  1  0 = 16 code
 * <br/> 0 0 0 0  0  1 = 32 ??
 * <br/> 0 0 1 0  1  0 = 4 + 16 = 20 = strikethrough + code
 * <br/> 1 1 0 0  0  0 = 3 + 16 = 19 = bold + italic
 * <br/> 1 0 0 0  0  0 = 1 + 16 = 17 = bold + code
 * <br/> 1 0 1 1  0  0 = 1 + 4 + 8 = 13 = bold + strikethrough + underline
 * <br/> 1 0 1 1  1  0 = 1 + 4 + 8 + 16 = 29 = bold + strikethrough + underline + code
 *
 * @return string[] array($format_prefix, $format_suffix)
 */
function findFormatWrappers($format)
{
	if(!$format) {
		$format = "";
	}

	$format_prefix = "";
	$format_suffix = "";
	switch($format) {
		// todo: complete combinations (preferable in a smart way)
		case "":
		case 0:
			break;
		case 1: // bold
			$format_prefix = "**";
			$format_suffix = "**";
			break;
		case 2: // italic
			$format_prefix = "_";
			$format_suffix = "_";
			break;
		case 3: // bold + italic
			$format_prefix = "**_";
			$format_suffix = "_**";
			break;
		case 4: // strikethrough
			$format_prefix = "~~";
			$format_suffix = "~~";
			break;
		case 8: // underline
			$format_prefix = "<u>";
			$format_suffix = "</u>";
			break;
		case 9: // bold + underline
			$format_prefix = "**<u>";
			$format_suffix = "</u>**";
			break;
		case 13: // bold + strikethrough + underline
			$format_prefix = "**~~<u>";
			$format_suffix = "</u>~~**";
			break;
		case 16: // code
			$format_prefix = "`";
			$format_suffix = "`";
			break;
		case 17: // bold + code
			$format_prefix = "**`";
			$format_suffix = "`**";
			break;
		case 20: // code + strikethrough
			$format_prefix = "`";
			$format_suffix = "`";
			break;
		case 29: // bold + strikethrough + underline + code
			$format_prefix = "**~~<u>`";
			$format_suffix = "`</u>~~**";
			break;
		default:
			$format_prefix = "#error: text-format-unknown_format='$format'";
	}
	return array($format_prefix, $format_suffix);
}

foreach($notes as $note_uuid => $note_data) {

	/*
	YAML, useful for WYSIWYM markdown metadata

	WARNING: I'm not going to use a YAML lib to keep this small, however this means the resulting YAML could be malformed as it's not being validated/parsed.

	NOTE:

	Zettlr uses `keywords` rather than `tags` in YAML.

	Created time will be in YMAL, modified times are applied to the file itself, not in YAML to keep it a little cleaner.

	Note `id` is used for ZettelKasten method and other universal/wiki-style note inter-linking schemes, to ensure link persistence across title changes. It's just the creation date in a tighter format down to the second. Due to note creation inconstancies, if a note ID already exists already, it'll be incremented by 1 second until it's unique. ¯\_(ツ)_/¯

	$filename will be the note title sanitized and the id... to avoid collisions and satisfy the zettelkasten/casual fans - maybe there will be filename options in the future?
	*/

	// only take real notes, not empty tag references to non-existent notes
	if(isset($note_data['created_at'])) {

		// create unique Zettel-style timestamp IDs.
		$note_id_prefix = '';
		$note_seconds = strtotime($note_data['created_at']);

		$note_id = $note_id_prefix.date("YmdHis", $note_seconds);
		while(isset($note_ids[$note_id])) {
			$note_seconds++;
			$note_id = $note_id_prefix.date("YmdHis", $note_seconds);
		}
		$note_ids[$note_id] = true;


		// manual tag YAML
		if(!empty($note_data['tags'])) {
			$note_tags_yaml = "tags:\n";
			foreach($note_data['tags'] as $note_tag_title => $value) {
				$note_tags_yaml .= "  - $note_tag_title\n";
			}

		}
		else $note_tags_yaml = '';

		if(isset($note_data['status'])) $note_status_yaml = "status: $note_data[status]\n";
		else $note_status_yaml = '';

		// manual note YAML
		$note_yaml = "---\ntitle: $note_data[title]\ncreated: $note_data[created_at]\nupdated: $note_data[updated_at]\nuuid: $note_uuid\nid: $note_id\n$note_tags_yaml$note_status_yaml---\n\n";

		$filename = preg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '-', $note_data['title'])." $note_id.md";

		$note_content = $note_yaml.parseContent($note_data, $filename);

		// she lives... 👹
		$write_note = file_put_contents($export_path.$filename, $note_content);
		if(!$write_note) echo "Error: $note_uuid failed to write.";
		else echo "Exported '$filename' ($note_uuid)!\n\n";

		// modification time
		touch($export_path.$filename, strtotime($note_data['updated_at']));

		$exported_count++;

	}

}

echo "Exported $exported_count Notes to: $export_path\n";
echo "Please also check the .md files for '#error:' to check if things failed";

?>

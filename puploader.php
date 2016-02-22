<?php

$key1             = '1234';   // the POST request must have a parameter with this name,
$key2             = '1234';   // and this value. works as a password. also recommended is to keep this php file in a secret folder with a secret name.
$max_size         = 0;        // max filesize in bytes.
$hash_algo        = 'sha256'; // hash algorithm used to generate filenames and check for duplicates.
$min_len          = 4;        // minimum length of a generated filename.
$default_name     = '_';      // if a file in the POST request has this as the filename, ignore it and go straight for a generated name.
$subfolder_direct = 'd/';     // direct (or default) folder, when there is no matching mime type.
$subfolder_image  = 'i/';     // image folder, for files with an image mime type.
$subfolder_video  = 'v/';     // video folder, for files with a video mime type.
$subfolder_audio  = 'a/';     // audio folder, for files with an audio mime type.
$subfolder_text   = 't/';     // text folder, for files with a text mime type.
$root_url         = 'http://example/'; // when the file is uploaded, an url of form {root_url}{subfolder}{file} is returned.

if (isset($_POST[$key1]) && $_POST[$key1] === $key2 && isset($_FILES['file'])) {
	try {
		$files = array();
		foreach ($_FILES['file'] as $key1 => $value1)
			foreach ($value1 as $key2 => $value2)
				$files[$key2][$key1] = $value2;
		foreach ($files as $file) {
			if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $max_size) {
				$tries = 0;
				$hash = hash_file($hash_algo, $file['tmp_name']);
				$mime = mime_content_type($file['tmp_name']);
				$info = pathinfo($file['name']);

				if (isset($info['extension']) && strlen($info['extension']) > 0)
					$info['extension'] = '.' . $info['extension'];

				if ($info['filename'] === '_') {
					$info['filename'] = substr($hash, 0, $min_len);
					$tries += 1;
				}

				$subfolder = $subfolder_direct;
				if (
					$mime === 'image/png' ||
					$mime === 'image/jpeg' ||
					$mime === 'image/gif' ||
					$mime === 'image/bmp' ||
					$mime === 'image/tiff' ||
					$mime === 'image/webp' ||
					$mime === 'image/svg+xml'
				) {
					$subfolder = $subfolder_image;
				} else if (
					$mime === 'video/mp4' ||
					$mime === 'video/webm'
				) {
					$subfolder = $subfolder_video;
				} else if (
					$mime === 'audio/mpeg' || $mime === 'audio/mp3' ||
					$mime === 'audio/ogg' ||
					$mime === 'audio/mp4' ||
					$mime === 'audio/webm'
				) {
					$subfolder = $subfolder_audio;
				} else if (
					$mime === 'text/plain' ||
					$mime === 'text/html' ||
					$mime === 'text/css' ||
					$mime === 'application/javascript' ||
					$mime === 'application/xml' ||
					$mime === 'text/xml' ||
					$mime === 'application/json'
				) {
					$subfolder = $subfolder_text;
				}

				$try_name = $info['filename'] . $info['extension'];
				if (!file_exists("../$subfolder$try_name") && move_uploaded_file($file['tmp_name'], "../$subfolder$try_name")) {
					echo "$root_url$subfolder" . rawurlencode($try_name);
				} else {
					if ($tries === 0)
						$try_name = $info['filename'] . '-';
					else
						$try_name = '';
					$max_len = strlen($hash);
					while ($min_len + $tries < $max_len) {
						$try_name_hash = $try_name . substr($hash, 0, $min_len + $tries) . $info['extension'];
						if (!file_exists("../$subfolder$try_name_hash") && move_uploaded_file($file['tmp_name'], "../$subfolder$try_name_hash")) {
							echo "$root_url$subfolder" . rawurlencode($try_name_hash);
							break;
						}
						$tries++;
					}
				}
			}
		}
	} catch (Exception $e) {
		exit;
	}
} else {
	exit;
}

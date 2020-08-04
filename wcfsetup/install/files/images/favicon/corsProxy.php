<?php
/**
 * Serves the manifest.json to properly allow cross-domain (CORS) fetching.
 * 
 * @author	Alexander
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
$types = [
	'manifest' => [
		'filename' => 'manifest.json',
		'type' => 'application/json'
	]
];
if (!empty($_GET['type']) || !isset($types[$_GET['type']])) {
	// get parameters
	$type = $_GET['type'];
	$styleID = (!empty($_GET['styleID'])) ? intval($_GET['styleID']) : 'default';
	if ($styleID === 'default' || $styleID > 0) {
		if ($styleID === 'default') {
			$filename = 'default.' . $types[$type]['filename'];
		}
		else {
			$filename = '../style-'.$styleID.'/'.$types[$type]['filename'];
		}
		if (file_exists($filename)) {
			$filemtime = filemtime($filename);
			
			$etag = '"' . md5($filemtime . $filename) . '"';
			$clientEtag = (!empty($_SERVER['HTTP_IF_NONE_MATCH'])) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '';
			$clientLastModified = (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) ? trim($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
			$clientLastModified = @strtotime($clientLastModified);
			
			// ignore request if client seems to already have fetched this file
			if (($clientLastModified && $clientEtag) ? (($clientLastModified == $filemtime) && ($clientEtag == $etag)) : ($clientLastModified == $filemtime) ) {
				header("HTTP/1.1 304 Not Modified");
				exit;
			}
			
			$data = file_get_contents($filename);
			
			if ($styleID !== 'default') {
				$data = str_replace('src": "', 'src": "../style-'.$styleID."/", $data);
			}
			
			// send cache and type headers
			// allow font fetching from all domains (CORS)
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: ' . $types[$type]['type']);
			header('Cache-Control: max-age=31536000, private');
			header('ETag: ' . $etag);
			header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
			header('Content-Length: ' . strlen($data));
			
			die($data);
		}
		
		header("HTTP/1.1 404 Not Found");
		die("Unknown file '" . $filename . "' requested.");
	}
	
	header("HTTP/1.1 400 Bad Request");
	die("Invalid styleID '" . $styleID . "' given");
}

header("HTTP/1.1 400 Bad Request");
die("Missing type parameter");

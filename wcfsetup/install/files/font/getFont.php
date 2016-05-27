<?php
/**
 * Serves fonts to enforce caching and to properly allow cross-domain (CORS) fetching.
 * 
 * This script solves the following issues:
 *  - Firefox and Internet Explorer refuse to load fonts from different domains unless allowed by 'Access-Control-Allow-Origin'
 *  - Chrome sometimes does not properly cache fonts, resulting in strange rendering bugs
 * 
 * @author	Alexander Ebert, Sascha Greuel
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

// list of known font types
$types = [
	'eot' => 'application/vnd.ms-fontobject',
	'ttf' => 'application/octet-stream',
	'woff' => 'application/x-woff', // best supported, but this is not the right one according to http://www.w3.org/TR/WOFF/#appendix-b
	'woff2' => 'font/woff2' // the specs at http://dev.w3.org/webfonts/WOFF2/spec/ are not perfectly clear, but font/woff2 seems to be the most sane one and is currently used by Google Fonts
];

// get parameters
$type = $_GET['type'];
$font = (!empty($_GET['font']) ? basename($_GET['font']) : 'fontawesome-webfont');

if (!empty($type)) {
	if (isset($types[$type])) {
		if (file_exists($font . '.' . $type)) {
			$filename = $font . '.' . $type;
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
			
			// send cache and type headers
			// allow font fetching from all domains (CORS)
			header('Access-Control-Allow-Origin: *');
			header('Content-Type: ' . $types[$type]);
			header('Cache-Control: max-age=31536000, private');
			header('ETag: ' . $etag);
			header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
			header('Content-Length: ' . strlen($data));
			
			die($data);
		}
		
		header("HTTP/1.1 400 Bad Request");
		die("Invalid font '" . htmlentities($font) . "' given");
	}
	
	header("HTTP/1.1 400 Bad Request");
	die("Invalid type '" . htmlentities($type) . "' given");
}

header("HTTP/1.1 400 Bad Request");
die("Missing type parameter");

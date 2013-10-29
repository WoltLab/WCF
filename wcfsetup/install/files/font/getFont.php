<?php
/**
 * Serves fonts to enforce caching and to properly allow cross-domain (CORS) fetching.
 * 
 * This script solves the following issues:
 *  - Firefox and Internet Explorer refuse to load fonts from different domains unless allowed by 'Access-Control-Allow-Origin'
 *  - Chrome sometimes does not properly cache fonts, resulting in strange rendering bugs
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

// allow font fetching from all domains (CORS)
header('Access-Control-Allow-Origin: *');

// ignore request if client seems to already have fetched this file
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) || !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
	header("HTTP/1.1 304 Not Modified");
	exit;
}

// list of known font types
$types = array(
	'eot' => 'application/vnd.ms-fontobject',
	'woff' => 'application/x-woff', // best supported, but this is not the right one according to http://www.w3.org/TR/WOFF/#appendix-b
	'ttf' => 'application/octet-stream'
);

if (!empty($_GET['type'])) {
	if (isset($types[$_GET['type']])) {
		$filename = 'fontawesome-webfont.' . $_GET['type'];
		$data = file_get_contents($filename);
		
		// send cache and type headers
		header('Content-Type: ' . $types[$_GET['type']]);
		header('Cache-control: max-age=31536000, private');
		header('Expires: ' . gmdate("D, d M Y H:i:s", time() + 31536000) . ' GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($filename)) . ' GMT');
		header('Content-Length: ' . strlen($data));
		
		die($data);
	}
	
	die("Invalid type '" . $_GET['type'] . "' given");
}

die("Missing type parameter");

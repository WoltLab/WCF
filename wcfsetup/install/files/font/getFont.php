<?php
/**
 * Serves fonts to enforce caching and to properly allow cross-domain (CORS) fetching.
 * 
 * This script solves the following issues:
 *  - Firefox and Internet Explorer refuse to load fonts from different domains unless allowed by 'Access-Control-Allow-Origin'
 *  - Chrome sometimes does not properly cache fonts, resulting in strange rendering bugs
 * 
 * @author	Tim Duesterhus, Alexander Ebert, Sascha Greuel
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

// list of known font types
$types = [
	'eot' => 'application/vnd.ms-fontobject',
	'ttf' => 'application/octet-stream',
	'woff' => 'application/x-woff', // best supported, but this is not the right one according to http://www.w3.org/TR/WOFF/#appendix-b
	'woff2' => 'font/woff2' // the specs at http://dev.w3.org/webfonts/WOFF2/spec/ are not perfectly clear, but font/woff2 seems to be the most sane one and is currently used by Google Fonts
];

function badRequest($reason) {
	header("HTTP/1.1 400 Bad Request");
	header("Content-Type: text/plain");
	die($reason);
}

function notFound($reason = "Unable to find font.") {
	header("HTTP/1.1 404 Not Found");
	header("Content-Type: text/plain");
	die($reason);
}

if (empty($_GET['filename'])) {
	if (empty($_GET['type'])) {
		badRequest('Neither filename nor type is given.');
	}
	$filename = (!empty($_GET['font']) ? basename($_GET['font']) : 'fontawesome-webfont').'.'.$_GET['type'];
}
else {
	$filename = __DIR__.'/';
	if (!empty($_GET['family'])) {
		$filename .= 'families/'.basename($_GET['family']).'/';
	}
	$filename .= basename($_GET['filename']);
}

$type = pathinfo($filename, PATHINFO_EXTENSION);

if (!isset($types[$type])) {
	badRequest('Invalid type given.');
}

if (!is_readable($filename)) {
	notFound();
}

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
const MAX_AGE = 86400 * 14;

header('Access-Control-Allow-Origin: *');
header('Content-Type: ' . $types[$type]);
header('Cache-Control: max-age=' . MAX_AGE . ', public');
header('ETag: ' . $etag);
header('Expires: ' . gmdate("D, d M Y H:i:s", time() + MAX_AGE) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
header('Content-Length: ' . strlen($data));

die($data);

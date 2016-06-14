<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
// @codingStandardsIgnoreFile
// get url
$url = '';
if (isset($_GET['url'])) $url = htmlspecialchars(str_replace(';', '%3B', trim($_GET['url'])));
if (empty($url)) exit;
// check url
$testURL = preg_replace('/[^a-z0-9:\/]+/', '', strtolower($url));
if (strpos($testURL, 'script:') !== false || !preg_match('~^https?://~', $testURL)) {
	exit;
}
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
	<title>Dereferer</title>
	<meta http-equiv="refresh" content="0;URL=<?php echo $url; ?>">
</head>
<body>
	<p><a href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
</body>
</html>
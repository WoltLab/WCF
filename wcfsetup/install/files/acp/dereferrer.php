<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category 	Community Framework
 */
// get url
$url = '';
if (isset($_GET['url'])) $url = htmlspecialchars(str_replace(';', '', trim($_GET['url'])));
if (empty($url)) exit;
// check url
$testURL = preg_replace('/[^a-z0-9:\/]+/', '', strtolower($url));
if (strpos($testURL, 'script:') !== false || !preg_match('/^https?:\/\//', $testURL)) {
	exit;
}
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="de"><head>
	<title>Dereferer</title>
	<meta http-equiv="refresh" content="0;URL=<?php echo $url; ?>" />
</head>
<body>
	<p><a href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
</body>
</html>
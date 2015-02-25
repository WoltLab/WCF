<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WoltLab Community Framework 2.1 System Requirements</title>
</head>
<body>
<?php
/**
 * Tests the support of PHP 5.3.2 or greater.
 * ><p><b>Support for PHP is missing.<br />PHP Unterst&uuml;tzung nicht gefunden</b></p> <!--
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
// @codingStandardsIgnoreFile
// check php version
// php version
$phpVersion = phpversion();
$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $phpVersion);
$neededPhpVersion = '5.3.2';
$configArray = @ini_get_all();
if (!(version_compare($comparePhpVersion, $neededPhpVersion) >= 0)) {
	?>
	<p style="color:red;" >Your PHP version '<?php echo $phpVersion; ?>' is insufficient for installation of this software. PHP version <?php echo $neededPhpVersion; ?> or greater is required.<br />
	Ihre PHP Version '<?php echo $phpVersion; ?>' ist unzureichend f&uuml;r die Installation dieser Software. PHP Version <?php echo $neededPhpVersion; ?> oder h&ouml;her wird ben&ouml;tigt.</p>
	<?php
}

// check mbstring
else if (!extension_loaded('mbstring')) {
	?>
	<p>The 'mbstring' PHP extension is missing. Mbstring is required for a stable work of this software.<br />
	Die 'mbstring' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check libxml
else if (!extension_loaded('libxml')) {
	?>
	<p>The 'libxml' PHP extension is missing. Libxml is required for a stable work of this software.<br />
	Die 'libxml' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check DOM
else if (!extension_loaded('dom')) {
	?>
	<p>The 'DOM' PHP extension is missing. DOM is required for a stable work of this software.<br />
	Die 'DOM' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check zlib extension
else if (!extension_loaded('zlib')) {
	?>
	<p>The 'zlib' PHP extension is missing. ZLib is required for a stable work of this software.<br />
	Die 'zlib' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check PDO extension
else if (!extension_loaded('pdo')) {
	?>
	<p>The 'PDO' PHP extension is missing. PDO is required for a stable work of this software.<br />
	Die 'PDO' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check PDO MySQL extension
else if (!extension_loaded('pdo_mysql')) {
	?>
	<p>The 'PDO_MYSQL' PHP extension is missing. PDO_MYSQL is required for a stable work of this software.<br />
	Die 'PDO_MYSQL' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check JSON extension
else if (!extension_loaded('json')) {
	?>
	<p>The 'JSON' PHP extension is missing. JSON is required for a stable work of this software.<br />
	Die 'JSON' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check PCRE extension
else if (!extension_loaded('pcre')) {
	?>
	<p>The 'PCRE' PHP extension is missing. PCRE is required for a stable work of this software.<br />
	Die 'PCRE' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check safemode
else if ((is_array($configArray) && !empty($configArray['safe_mode']['local_value']) && $configArray['safe_mode']['local_value'] != 'off') || (@ini_get('safe_mode') && ini_get('safe_mode') != 'off')) {
	?>
	<p>PHP Safemode is enabled. You must disable it to install this software.<br />
	Der PHP Safemode ist aktiviert. F&uuml;r den Betrieb der Software muss der Safemode deaktiviert sein.</p>
	<?php
}

// everything is fine
else {
	?>
	<p style="color:green;">PHP <?php echo $neededPhpVersion; ?> or greater is available. You can <a href="install.php">start</a> the installation now.<br />
	PHP <?php echo $neededPhpVersion; ?> oder h&ouml;her wurde gefunden. Sie k&ouml;nnen mit der Installation <a href="install.php">beginnen</a>.</p>
	<?php
}
?>
</body>
</html>

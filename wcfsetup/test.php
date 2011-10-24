<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>PHP5 Check</title>
</head>
<body>
<?php
/**
 * Tests the support of php5.0.5 or greater.
 * ><p><b>Support for PHP is missing.<br />PHP Unterst&uuml;tzung nicht gefunden</b></p> <!--
 * 
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
// check php version
// php version
$phpVersion = phpversion();
$comparePhpVersion = preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $phpVersion);
if (!(version_compare($comparePhpVersion, '5.3.0') >= 0)) {
	?>
	<p>Your PHP version '<?php echo $phpVersion; ?>' is insufficient for installation of this software. PHP version 5.2.0 or greater is required.<br />
	Ihre PHP Version '<?php echo $phpVersion; ?>' ist unzureichend f&uuml;r die Installation dieser Software. PHP Version 5.2.0 oder h&ouml;her wird ben&ouml;tigt.</p>
	<?php
}

// check ze1_compatibility_mode
else if (ini_get('zend.ze1_compatibility_mode')) {
	?>
	<p>The option 'zend.ze1_compatibility_mode' is enabled. Please disable the option in your PHP configuration (php.ini) for a stable work of this software.<br />
	Die Einstellung 'zend.ze1_compatibility_mode' ist aktiv. F&uuml;r einen einwandfreien Betrieb dieser Software muss die Einstellung in der PHP-Konfiguration (php.ini) deaktiviert werden.</p>
	<?php
}

// check simplexml
else if (!function_exists('simplexml_load_file')) {
	?>
	<p>The 'simplexml' PHP extension is missing. Simplexml is required for a stable work of this software.<br />
	Die 'simplexml' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// check zlib extension
else if (!function_exists('gzopen')) {
	?>
	<p>The 'zlib' PHP extension is missing. ZLib is required for a stable work of this software.<br />
	Die 'zlib' Erweiterung f&uuml;r PHP wurde nicht gefunden. Diese Erweiterung ist f&uuml;r den Betrieb der Software notwendig.</p>
	<?php
}

// 
else {
	?>
	<p>PHP5 or greater is available. You can <a href="install.php">start</a> the installation now.<br />
	PHP5 oder h&ouml;her wurde gefunden. Sie k&ouml;nnen mit der Installation <a href="install.php">beginnen</a>.</p>
	<?php
}
?>
</body>
</html>
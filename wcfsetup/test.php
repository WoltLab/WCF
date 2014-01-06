<?php
if (isset($_SERVER['PATH_INFO']) || isset($_SERVER['ORIG_PATH_INFO'])) {
	$pathInfo = null;
	if (isset($_SERVER['PATH_INFO'])) {
		$pathInfo = $_SERVER['PATH_INFO'];
	}
	else if (isset($_SERVER['ORIG_PATH_INFO'])) {
		$pathInfo = $_SERVER['ORIG_PATH_INFO'];
			
		// in some configurations ORIG_PATH_INFO contains the path to the file
		// if the intended PATH_INFO component is empty
		if (!empty($pathInfo)) {
			if (isset($_SERVER['SCRIPT_NAME']) && ($pathInfo == $_SERVER['SCRIPT_NAME'])) {
				$pathInfo = '';
			}
				
			if (isset($_SERVER['PHP_SELF']) && ($pathInfo == $_SERVER['PHP_SELF'])) {
				$pathInfo = '';
			}
				
			if (isset($_SERVER['SCRIPT_URL']) && ($pathInfo == $_SERVER['SCRIPT_URL'])) {
				$pathInfo = '';
			}
		}
	}
	
	if (!empty($pathInfo)) {
		if ($pathInfo == '/test/') {
			echo "PASSED";
		}
		else {
			@header("HTTP/1.0 500 Internal Server Error");
			echo "FAILED";
		}
		
		exit;
	}
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WoltLab Community Framework 2.0 System Requirements</title>
</head>
<body>
<?php
/**
 * Tests the support of PHP 5.3.2 or greater.
 * ><p><b>Support for PHP is missing.<br />PHP Unterst&uuml;tzung nicht gefunden</b></p> <!--
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
	// check for broken nginx setups
	$isNginx = false;
	if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
		$isNginx = true;
	}
	$isNginx = true;
	if ($isNginx) { ?>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.1/jquery.min.js"></script>
		<script>
			$(function() {
				$.ajax('test.php/test/', {
					dataType: 'html',
					error: function() {
						$('#worker').remove();
						$('#nginxFailure').show();
					},
					success: function() {
						if (arguments[0] == 'PASSED') {
							$('#worker').remove();
							$('#nginx').show();
						}
						else {
							$('#worker').remove();
							$('#nginxFailure').show();
						}
					}
				});
			});
		</script>
		<p id="worker">Please wait &hellip;<br />Bitte warten &hellip;</p>
		<p id="nginxFailure" style="color:red; display: none;">
			You are running nginx, but your current configuration prevents a successful installation. Please fix the PATH_INFO support for PHP, guides can be found <a href="https://www.google.de/search?q=nginx+php+path_info" target="_blank">here</a>. If you're on a hosted solution, please ask your hosting company to fix their configuration.<br />
			<br />
			Sie verwenden nginx als Webserver, jedoch verhindert die aktuelle Konfiguration eine erfolgreiche Installation. Bitte aktivieren Sie die PATH_INFO-Unterst&uuml;zung f&uuml;r PHP, Anleitungen sind <a href="https://www.google.de/search?q=nginx+php+path_info" target="_blank">hier</a> verf&uuml;gbar. Sollten Sie nur einen Webspace gemietet haben, so wenden Sie sich bitte an Ihren Anbieter, damit dieser die fehlerhafte Konfiguration korrigiert.
		</p>
		<div id="nginx" style="display:none;">
	<?php
	}
	
	?>
	<p style="color:green;">PHP <?php echo $neededPhpVersion; ?> or greater is available. You can <a href="install.php">start</a> the installation now.<br />
	PHP <?php echo $neededPhpVersion; ?> oder h&ouml;her wurde gefunden. Sie k&ouml;nnen mit der Installation <a href="install.php">beginnen</a>.</p>
	<?php
	
	if ($isNginx) { ?></div><?php }
}
?>
</body>
</html>

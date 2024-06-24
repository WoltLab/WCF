<?php

// phpcs:disable PSR1.Files.SideEffects

$language = 'en';
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && \preg_match('~^de-[A-Z]+,de~', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $language = 'de';
}

if (isset($_GET['language']) && \in_array($_GET['language'], ['de', 'en'])) {
    $language = $_GET['language'];
}

const WSC_SRT_VERSION = '6.1.0';
$requiredExtensions = [
    'ctype',
    'dom',
    'exif',
    'gmp',
    'intl',
    'libxml',
    'mbstring',
    'openssl',
    'pdo',
    'pdo_mysql',
    'zlib',
];
$phpVersionLowerBound = '8.1.2';
$phpVersionUpperBound = '8.3.x';
$phrases = [
    'php_requirements' => [
        'de' => 'PHP',
        'en' => 'PHP Requirements',
    ],
    'php_version_success' => [
        'de' => 'PHP-Version %s',
        'en' => 'PHP version %s',
    ],
    'php_version_failure' => [
        'de' => 'Gefundene PHP-Version %s ist inkompatibel. PHP %s – %s wird benötigt.',
        'en' => 'PHP version %s is incompatible. PHP %s – %s is required.',
    ],
    'php_x64_success' => [
        'de' => '64-Bit-Unterstützung',
        'en' => '64-bit Support',
    ],
    'php_x64_failure' => [
        'de' => '64-Bit-Unterstützung fehlt',
        'en' => '64-bit support missing',
    ],
    'php_extension_success' => [
        'de' => 'Erweiterung %s vorhanden',
        'en' => '%s extension loaded',
    ],
    'php_extension_failure' => [
        'de' => 'Erweiterung %s fehlt',
        'en' => '%s extension missing',
    ],
    'php_extension_gd_or_imagick_failure' => [
        'de' => 'Erweiterung für Bildverarbeitung (GD oder Imagick) fehlt',
        'en' => 'Extension for image processing (GD or Imagick) missing',
    ],
    'php_extension_gd_or_imagick_webp_failure' => [
        'de' => 'Unterstützung für WebP-Grafiken in %s fehlt',
        'en' => 'Support for WebP images in %s missing',
    ],
    'php_memory_limit_success' => [
        'de' => 'Arbeitsspeicher-Limit %s',
        'en' => '%s memory limit',
    ],
    'php_memory_limit_failure' => [
        'de' => 'Arbeitsspeicher-Limit %s ist nicht ausreichend. 128 MiB oder mehr wird benötigt.',
        'en' => 'Memory limit %s is too low. It needs to be set to 128 MiB or more.',
    ],
    'php_opcache_failure' => [
        'de' => 'OPcache ist aktiviert, aber die erforderlichen Verwaltungsfunktionen (opcache_reset, opcache_invalidate) sind deaktiviert.',
        'en' => 'OPcache is enabled but the required management functions (opcache_reset, opcache_invalidate) are disabled.',
    ],
    'mysql_requirements' => [
        'de' => 'MySQL',
        'en' => 'MySQL Requirements',
    ],
    'mysql_version' => [
        'de' => 'Bitte stellen Sie sicher, dass MySQL 8.0.30+  oder MariaDB 10.5.15+ mit InnoDB-Unterstützung vorhanden ist.',
        'en' => 'Please make sure that MySQL 8.0.30+ or MariaDB 10.5.15+, with InnoDB support is available.',
    ],
    'tls_failure' => [
        'de' => 'Die Seite wird nicht über HTTPS aufgerufen. Wichtige Funktionen stehen dadurch nicht zur Verfügung, die für die korrekte Funktionsweise der Software erforderlich sind.',
        'en' => 'The page is not accessed via HTTPS. Important features that are required for the proper operation of the software are therefore not available.',
    ],
    'result' => [
        'de' => 'Ergebnis',
        'en' => 'Summary',
    ],
    'result_success' => [
        'de' => 'Alle Systemvoraussetzungen sind erfüllt. Sie können die Installation von WoltLab Suite beginnen.',
        'en' => 'Your system fulfills all of WoltLab Suite\'s system requirements. You are ready to install WoltLab Suite!',
    ],
    'result_failure' => [
        'de' => 'Die Systemvoraussetzungen sind nicht erfüllt. Bitte beachten Sie die oben genannten Informationen. Wenden Sie sich ggf. an Ihren Webhoster oder Ihren Serveradministrator.',
        'en' => 'The system requirements are not met. Please note the above information and contact your web host or server administrator if necessary.',
    ],
    'button_start_installation' => [
        'de' => 'Installation starten',
        'en' => 'Start Installation',
    ],
];
function getPhrase($phrase, array $values = [])
{
    global $language, $phrases;

    if (!isset($phrases[$phrase]) || !isset($phrases[$phrase][$language])) {
        return "[unknown:{$phrase}]";
    }

    return \vsprintf($phrases[$phrase][$language], $values);
}
function checkPHPVersion()
{
    global $phpVersionLowerBound, $phpVersionUpperBound;

    $comparePhpVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', \PHP_VERSION);

    return \version_compare($comparePhpVersion, $phpVersionLowerBound, '>=')
        && \version_compare($comparePhpVersion, \str_replace('x', '999', $phpVersionUpperBound), '<=');
}
function getMemoryLimit()
{
    $memoryLimit = \ini_get('memory_limit');

    // no limit
    if ($memoryLimit == "-1") {
        return -1;
    } elseif (\function_exists('ini_parse_quantity')) {
        return \ini_parse_quantity($memoryLimit);
    } else {
        // completely numeric, PHP assumes byte
        if (\is_numeric($memoryLimit)) {
            return $memoryLimit;
        }

        // PHP supports 'K', 'M' and 'G' shorthand notation
        if (\preg_match('~^(\d+)\s*([KMG])$~i', $memoryLimit, $matches)) {
            switch (\strtoupper($matches[2])) {
                case 'K':
                    return $matches[1] * 1024;

                case 'M':
                    return $matches[1] * 1024 * 1024;

                case 'G':
                    return $matches[1] * 1024 * 1024 * 1024;
            }
        }
    }

    return 0;
}
function checkMemoryLimit()
{
    $memoryLimit = getMemoryLimit();
    return $memoryLimit == -1 || $memoryLimit >= 128 * 1024 * 1024;
}
function checkX64()
{
    return \PHP_INT_SIZE == 8;
}
function formatFilesizeBinary($byte): string
{
    $symbol = 'Byte';
    if ($byte >= 1024) {
        $byte /= 1024;
        $symbol = 'KiB';
    }
    if ($byte >= 1024) {
        $byte /= 1024;
        $symbol = 'MiB';
    }
    if ($byte >= 1024) {
        $byte /= 1024;
        $symbol = 'GiB';
    }
    if ($byte >= 1024) {
        $byte /= 1024;
        $symbol = 'TiB';
    }

    return \floor($byte) . ' ' . $symbol;
}
function checkResult()
{
    global $requiredExtensions;

    if (!checkPHPVersion() || !checkX64() || !checkMemoryLimit() || !checkOpcache() || !checkTls()) {
        return false;
    }

    foreach ($requiredExtensions as $extension) {
        if (!\extension_loaded($extension)) {
            return false;
        }
    }

    $hasSufficientImageLibrary = false;
    if (\extension_loaded('imagick') && \in_array('WEBP', \Imagick::queryFormats())) {
        $hasSufficientImageLibrary = true;
    }

    if (\extension_loaded('gd') && !empty(\gd_info()['WebP Support'])) {
        $hasSufficientImageLibrary = true;
    }

    if (!$hasSufficientImageLibrary) {
        return false;
    }

    return true;
}
function checkInstallFile()
{
    return @\file_exists('install.php');
}
function checkOpcache()
{
    if (\extension_loaded('Zend Opcache') && \ini_get('opcache.enable')) {
        if (!\function_exists('\opcache_reset') || !\function_exists('\opcache_invalidate')) {
            return false;
        }
    }

    return true;
}
function checkTls(): bool
{
    // @see \wcf\system\request\RouteHandler::secureConnection()
    if (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
        || $_SERVER['SERVER_PORT'] == 443
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    ) {
        return true;
    }

    // @see \wcf\system\request\RouteHandler::secureContext()
    $host = $_SERVER['HTTP_HOST'];
    if ($host === '127.0.0.1' || $host === 'localhost' || \str_ends_with($host, '.localhost')) {
        return true;
    }

    return false;
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>WoltLab Suite System Requirements Test</title>

    <style>
        html {
            background-color: #2D2D2D;
            box-sizing: border-box;
            color: #c0c0c0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            font-size: 14px;
            line-height: 1.5;
        }

        *,
        *::before,
        *::after {
            box-sizing: inherit;
            min-width: 0;
        }

        a {
            color: inherit;
        }

        .layout-boundary {
            margin: 50px auto;
            max-width: 980px;
        }

        main {
            background-color: #3D3D3D;
            border-radius: 3px;
            padding: 40px 20px;
        }

        header {
            align-items: flex-end;
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        header>img {
            flex: 0 auto;
        }

        .language-switcher {
            flex: 0 auto;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .language-switcher>li {
            display: inline;
        }

        .language-switcher>li+li {
            border-left: 1px solid #444444;
            margin-left: 10px;
            padding-left: 10px;
        }

        .language {
            font-size: 18px;
        }

        .language.active {
            color: inherit;
            pointer-events: none;
            text-decoration: none;
        }

        .language:not(.active) {
            color: #fff;
        }

        footer {
            color: #9D9D9D;
            padding-top: 10px;
            text-align: right;
        }

        footer>a {
            color: inherit;
            text-decoration: none;
        }

        h2,
        h3 {
            margin: 0 0 10px 0;
            font-weight: 300;
            padding: 0;
        }

        h2:not(:first-child),
        h3:not(:first-child) {
            margin-top: 40px;
        }

        h2 {
            font-size: 32px;
        }

        h3 {
            font-size: 24px;
        }

        ul.system-requirements {
            padding: 0;
        }

        ul.system-requirements li {
            list-style: none;
            margin-bottom: 10px;
            padding: 0 20px;
        }

        ul.system-requirements li::before {
            font-family: Georgia, "Times New Roman", serif;
            margin-right: 10px;
        }

        li.success {
            color: #00c291;
        }

        li.success::before {
            content: '✔';
        }

        li.failure {
            color: #f08f84;
        }

        li.failure::before {
            content: '✘';
        }

        li.info {
            color: #63b0e3;
        }

        li.info::before {
            content: '✔';
        }

        p.success,
        p.failure {
            border-radius: 3px;
            color: #fff;
            padding: 10px 20px;
        }

        p.success::before,
        p.failure::before {
            font-family: Georgia, "Times New Roman", serif;
            margin-right: 10px;
        }

        p.success {
            background-color: #008563;
        }

        p.success::before {
            content: '✔';
        }

        p.failure {
            background-color: #de2f1b;
        }

        p.failure::before {
            content: '✘';
        }

        .button {
            background-color: #375a7f;
            border-radius: 3px;
            border-width: 0;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
            padding: 15px 30px;
            text-decoration: none;
            vertical-align: middle;
            text-transform: uppercase;
        }

        .button:hover {
            background-color: #2b4764;
        }
    </style>
</head>

<body>
    <div class="layout-boundary">
        <header>
            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAjIAAABQCAMAAAA0sW8TAAACxFBMVEUAAAD////////////////////////////////////////////////////////////a3ADk0RJzuQDp0hgAEiPj3wAwkHoAiL3YWQDVVQByuADaXABxuADWVwDa3AC2xwjs2xDUVADc3QD5ogPQ2AD3wBFzuQDZWgAEm8hwuAB4uQD65wAAg7nXVwAAcKhwuACAuCcJkr0LBADUVAAAir+4yQgwpqj4zFwAerEHChAAAAD/vCb/y1fj3wAAEB/5jACwwwX/uBwACRKBNgHDwhQAj8PueAD65wDiuxT+swcHiLP8qQPUVADfbQUAS4jL1wDe3QC/ygoASYKYxgAARIEAS4gAJ0395wBqKgAAAAD///9wuADUVAAAebDi3wDm4ADr4gDu4wAAfbQAdazg3gB8ugD35gDx4wChvgDK1gD05AClvwB1uQDc3QAAir7o4QDAwhPF1AHP2ADdYACVvQDgZQDjagDZWwDYxSXexinjxy7TxSLPxB7W2wAAhbsAWZQAgreJuwCpwAKavgAAYZvLxBvA0wORvADrdgABcKgAaqOEuwAAjsMAcqkAZ6AAXZiMvQDvewEAk8cAbaXpxzKBugD1hADmbgDXVwAAVY8ATInHwxiuwAX75wCdvwCxwQgAR4TEwxYAUY26wg7T2gD/nwAAZJ7wyDe3wQ3J0ge60ATpcQD/mQL7yUC0wQoBBAf/lAD5igEAQn/yfwC9whD/pQD9jwD/rQEAMF0AHDi1zQSvxwMAN2v/y1P/0WUAJksBP3abtwJ+oAD9uBeKqgH/2HmjyACTsQKWxQD/vzBDteYsk8URocR/vnxyscgeh7tYl7A+noePn3jGvFN3aU+nwknMxThKHgDM5NUVf7Glw6xRt5nm1pIpb5BSXlpfr1b6sVJyz/Hu4sna07LPx4AdXH6xrHaoczuqSQOD2vl3tlrilkluJ0CCAAAAWnRSTlMAv0CA7xBgIJ/fMM+PUHCv/hC8MP5AEC4Q6OcnfUiknIODdWVbIJlwXlgq7+uoXkD998/HnHj89sx2ENDPuqenYFAk+urKyr+6pICAYPfv39fPv7+QaN/fn1CakgmdAAAR5UlEQVR42uzYQQqDMBBG4TlCRdwWKbrKGVx7hO5iBFFw44m8a9UQomXU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" style="height: 40px; width: 281px" alt="WoltLab Suite">
            <ul class="language-switcher">
                <li><a href="./test.php?language=de" class="language<?= ($language === 'de' ? ' active' : '') ?>">Deutsch</a></li>
                <li><a href="./test.php?language=en" class="language<?= ($language === 'en' ? ' active' : '') ?>">English</a></li>
            </ul>
        </header>

        <main>
            <h2>WoltLab Suite System Requirements Test</h2>

            <h3><?= getPhrase('php_requirements') ?></h3>

            <ul class="system-requirements">
                <?php if (checkPHPVersion()) { ?>
                    <li class="success"><?= getPhrase('php_version_success', [\PHP_VERSION]) ?></li>

                    <?php if (checkX64()) { ?>
                        <li class="success"><?= getPhrase('php_x64_success') ?></li>
                    <?php } else { ?>
                        <li class="success"><?= getPhrase('php_x64_failure') ?></li>
                    <?php } ?>

                    <?php foreach ($requiredExtensions as $extension) { ?>
                        <?php if (\extension_loaded($extension)) { ?>
                            <li class="success"><?= getPhrase('php_extension_success', [$extension]) ?></li>
                        <?php } else { ?>
                            <li class="failure"><?= getPhrase('php_extension_failure', [$extension]) ?></li>
                        <?php } ?>
                    <?php } ?>

                    <?php if (\extension_loaded('imagick') && \in_array('WEBP', \Imagick::queryFormats())) { ?>
                        <li class="success"><?= getPhrase('php_extension_success', ['Imagick']) ?></li>
                    <?php } elseif (\extension_loaded('gd')) { ?>
                        <?php if (!empty(\gd_info()['WebP Support'])) { ?>
                            <li class="success"><?= getPhrase('php_extension_success', ['GD']) ?></li>
                        <?php } else { ?>
                            <li class="failure"><?= getPhrase('php_extension_gd_or_imagick_webp_failure', ['GD']) ?></li>
                        <?php } ?>
                    <?php } else { ?>
                        <li class="failure"><?= getPhrase('php_extension_gd_or_imagick_failure') ?></li>
                    <?php } ?>

                    <?php if (checkMemoryLimit()) { ?>
                        <li class="success"><?= getPhrase('php_memory_limit_success', [formatFilesizeBinary(getMemoryLimit())]) ?></li>
                    <?php } else { ?>
                        <li class="failure"><?= getPhrase('php_memory_limit_failure', [formatFilesizeBinary(getMemoryLimit())]) ?></li>
                    <?php } ?>

                    <?php if (!checkOpcache()) { ?>
                        <li class="failure"><?= getPhrase('php_opcache_failure') ?></li>
                    <?php } ?>

                    <?php if (!checkTls()) { ?>
                        <li class="failure"><?= getPhrase('tls_failure') ?></li>
                    <?php } ?>
                <?php } else { ?>
                    <li class="failure"><?= getPhrase('php_version_failure', [\PHP_VERSION, $phpVersionLowerBound, $phpVersionUpperBound]) ?></li>
                <?php } ?>
            </ul>

            <h3><?= getPhrase('mysql_requirements') ?></h3>

            <ul class="system-requirements">
                <li class="info"><?= getPhrase('mysql_version') ?></li>
            </ul>

            <h2><?= getPhrase('result') ?></h2>

            <?php if (checkResult()) { ?>
                <p class="success"><?= getPhrase('result_success') ?></p>
            <?php } else { ?>
                <p class="failure"><?= getPhrase('result_failure') ?></p>
            <?php } ?>

            <?php if (checkInstallFile()) { ?>
                <p style="margin-top: 50px; text-align: center;"><a href="install.php" class="button"><?= getPhrase('button_start_installation') ?></a></p>
            <?php } ?>
        </main>
        <footer>
            <a href="https://www.woltlab.com">WoltLab Suite System Requirements Test v<?= WSC_SRT_VERSION ?></a>
        </footer>
    </div>
</body>

</html>
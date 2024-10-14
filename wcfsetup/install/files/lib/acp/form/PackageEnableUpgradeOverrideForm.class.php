<?php

namespace wcf\acp\form;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\RejectEverythingFormField;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Allows enabling the package upgrade override.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 */
final class PackageEnableUpgradeOverrideForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'enable';

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.package.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canUpdatePackage'];

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (WCF::AVAILABLE_UPGRADE_VERSION === null) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $issues = $this->getIssuesPreventingUpgrade();

        if ($issues === [] || $this->isEnabled()) {
            $this->form->appendChildren([
                TemplateFormNode::create('issues')
                    ->templateName('packageEnableUpgradeOverrideSuccess'),
                BooleanFormField::create('enable')
                    ->label('wcf.acp.package.enableUpgradeOverride.enable')
                    ->value(PackageUpdateServer::isUpgradeOverrideEnabled()),
            ]);
        } else {
            $this->form->addDefaultButton(false);
            $this->form->appendChildren([
                TemplateFormNode::create('issues')
                    ->templateName('packageEnableUpgradeOverrideIssues')
                    ->variables([
                        'issues' => $issues,
                    ]),
                RejectEverythingFormField::create(),
            ]);
        }
    }

    private function isEnabled()
    {
        if (!isset($this->isEnabled)) {
            $this->isEnabled = PackageUpdateServer::isUpgradeOverrideEnabled();
        }

        return $this->isEnabled;
    }

    private function getIssuesPreventingUpgrade()
    {
        $parameters = ['issues' => []];
        EventHandler::getInstance()->fireAction($this, 'getIssuesPreventingUpgrade', $parameters);

        $issues = [
            $this->checkMinimumPhpVersion(),
            $this->checkMaximumPhpVersion(),
            $this->checkRequiredPhpExtensions(),
            $this->checkMinimumDatabaseVersion(),
            $this->checkForTls(),
            ...$parameters['issues'],
        ];

        return \array_filter($issues);
    }

    private function checkMinimumPhpVersion(): ?array
    {
        // Minimum: PHP 8.1.2
        if (\PHP_VERSION_ID >= 80102) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Unzureichende PHP-Version',
                'description' => 'Es wird mindestens PHP 8.1.2 benötigt.',
            ];
        } else {
            return [
                'title' => 'Insufficient PHP version',
                'description' => 'PHP 8.1.2 or newer is required.',
            ];
        }
    }

    private function checkMaximumPhpVersion(): ?array
    {
        // Maximum: PHP 8.3.x
        if (\PHP_VERSION_ID < 80399) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Inkompatible PHP-Version',
                'description' => 'Es wird nur PHP 8.1, 8.2 oder 8.3 unterstützt.',
            ];
        } else {
            return [
                'title' => 'Incompatible PHP version',
                'description' => 'Only PHP 8.1, 8.2 or 8.3 are supported.',
            ];
        }
    }

    private function checkRequiredPhpExtensions(): ?array
    {
        $requiredExtensions = [
            'ctype',
            'dom',
            'exif',
            'gd',
            'gmp',
            'intl',
            'libxml',
            'mbstring',
            'openssl',
            'pdo_mysql',
            'pdo',
            'zlib',
        ];

        $missingExtensions = [];
        foreach ($requiredExtensions as $extension) {
            if (!\extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if ($missingExtensions === []) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Fehlende PHP-Erweiterungen',
                'description' => 'Die folgenden PHP-Erweiterungen werden für den Betrieb benötigt: ' . \implode(', ', $missingExtensions),
            ];
        } else {
            return [
                'title' => 'Missing PHP extensions',
                'description' => 'The following PHP extensions are required for the operation: ' . \implode(', ', $missingExtensions),
            ];
        }
    }

    private function checkMinimumDatabaseVersion(): ?array
    {
        $sqlVersion = WCF::getDB()->getVersion();
        $compareSQLVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);

        if (\stripos($sqlVersion, 'MariaDB') !== false) {
            $databaseName = "MariaDB {$compareSQLVersion}";
            $expectedVersion = '10.5.15';
            $alternativeDatabase = 'MySQL 8.0.30+';
        } else {
            $databaseName = "MySQL {$compareSQLVersion}";
            $expectedVersion = '8.0.30';
            $alternativeDatabase = 'MariaDB 10.5.15+';
        }

        $result = (\version_compare(
            $compareSQLVersion,
            $expectedVersion,
        ) >= 0);

        if ($result) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Inkompatible Datenbank-Version',
                'description' => "Die verwendete Datenbank {$databaseName} ist zu alt, es wird mindestens die Version {$expectedVersion} benötigt, alternativ {$alternativeDatabase}.",
            ];
        } else {
            return [
                'title' => 'Incompatible database version',
                'description' => "The database {$databaseName} being used is too old, version {$expectedVersion} or newer is required, alternatively {$alternativeDatabase}.",
            ];
        }
    }

    private function checkForTls(): ?array
    {
        if (RouteHandler::secureConnection()) {
            return null;
        }

        // @see RouteHandler::secureContext()
        $host = $_SERVER['HTTP_HOST'];
        if ($host === '127.0.0.1' || $host === 'localhost' || \str_ends_with($host, '.localhost')) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Aufruf über HTTPS',
                'description' => 'Die Seite wird nicht über HTTPS aufgerufen. Wichtige Funktionen stehen dadurch nicht zur Verfügung, die für die korrekte Funktionsweise der Software erforderlich sind.',
            ];
        } else {
            return [
                'title' => 'Access using HTTPS',
                'description' => 'The page is not accessed via HTTPS. Important features that are required for the proper operation of the software are therefore not available.',
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $overrideKey = \sprintf(
            "%s\0upgradeOverride_%s",
            PackageUpdateServer::class,
            WCF::AVAILABLE_UPGRADE_VERSION,
        );

        $formData = $this->form->getData();
        if ($formData['data']['enable']) {
            $this->isEnabled = true;
            RegistryHandler::getInstance()->set('com.woltlab.wcf', $overrideKey, \TIME_NOW);
        } else {
            $this->isEnabled = false;
            RegistryHandler::getInstance()->delete('com.woltlab.wcf', $overrideKey);
        }

        // Clear the legacy override.
        RegistryHandler::getInstance()->delete('com.woltlab.wcf', self::class . "\0upgradeOverride");
        RegistryHandler::getInstance()->delete('com.woltlab.wcf', self::class . "\0upgradeOverride_6.0");

        PackageUpdateServer::resetAll();

        $this->form->cleanup();
        $this->buildForm();

        $this->saved();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'availableUpgradeVersion' => WCF::AVAILABLE_UPGRADE_VERSION,
        ]);
    }
}

<?php

namespace wcf\acp\form;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\RejectEverythingFormField;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\registry\RegistryHandler;
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

        if (empty($issues) || $this->isEnabled()) {
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
        $issues = [];

        $phpVersion = \PHP_VERSION;
        $neededPhpVersion = '8.1.2';
        if (!\version_compare($phpVersion, $neededPhpVersion, '>=')) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $title = 'Veraltete PHP-Version';
                $description = "Ihre PHP-Version '{$phpVersion}' ist unzureichend f&uuml;r die Installation dieser Software. PHP-Version {$neededPhpVersion} oder h&ouml;her wird ben&ouml;tigt.";
            } else {
                $title = 'Outdated PHP Version';
                $description = "Your PHP version '{$phpVersion}' is insufficient for installation of this software. PHP version {$neededPhpVersion} or greater is required.";
            }

            $issues[] = [
                'title' => $title,
                'description' => $description,
            ];
        }

        $sqlVersion = WCF::getDB()->getVersion();
        $compareSQLVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
        if (\stripos($sqlVersion, 'MariaDB') !== false) {
            $neededSqlVersion = '10.5.12';
            $sqlFork = 'MariaDB';
        } else {
            $sqlFork = 'MySQL';
            $neededSqlVersion = '8.0.29';
        }

        if (!\version_compare($compareSQLVersion, $neededSqlVersion, '>=')) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $title = "Veraltete {$sqlFork}-Version";
                $description = "Ihre {$sqlFork}-Version '{$sqlVersion}' ist unzureichend f&uuml;r die Installation dieser Software. {$sqlFork}-Version {$neededSqlVersion} oder h&ouml;her wird ben&ouml;tigt.";
            } else {
                $title = "Outdated {$sqlFork} Version";
                $description = "Your {$sqlFork} version '{$sqlVersion}' is insufficient for installation of this software. {$sqlFork} version {$neededSqlVersion} or greater is required.";
            }

            $issues[] = [
                'title' => $title,
                'description' => $description,
            ];
        }

        return $issues;
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $formData = $this->form->getData();
        if ($formData['data']['enable']) {
            $this->isEnabled = true;
            RegistryHandler::getInstance()->set('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride", \TIME_NOW);
        } else {
            $this->isEnabled = false;
            RegistryHandler::getInstance()->delete('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride");
        }

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

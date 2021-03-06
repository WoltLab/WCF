<?php

namespace wcf\acp\form;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
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
 * @package WoltLabSuite\Core\Acp\Form
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
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $issues = $this->getIssuesPreventingUpgrade();

        if (empty($issues)) {
            $this->form->appendChildren([
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

    private function getIssuesPreventingUpgrade()
    {
        $issues = [];

        $phpVersion = \PHP_VERSION;
        $neededPhpVersion = '7.2.24';
        if (!\version_compare($phpVersion, $neededPhpVersion, '>=')) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $message = "Ihre PHP-Version '{$phpVersion}' ist unzureichend f&uuml;r die Installation dieser Software. PHP-Version {$neededPhpVersion} oder h&ouml;her wird ben&ouml;tigt.";
            } else {
                $message = "Your PHP version '{$phpVersion}' is insufficient for installation of this software. PHP version {$neededPhpVersion} or greater is required.";
            }

            $issues[] = $message;
        }

        $sqlVersion = WCF::getDB()->getVersion();
        $compareSQLVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
        if (\stripos($sqlVersion, 'MariaDB') !== false) {
            $neededSqlVersion = '10.1.44';
            $sqlFork = 'MariaDB';
        } else {
            $sqlFork = 'MySQL';
            if ($compareSQLVersion[0] === '5') {
                $neededSqlVersion = '5.7.31';
            } else {
                $neededSqlVersion = '8.0.19';
            }
        }

        if (!\version_compare($compareSQLVersion, $neededSqlVersion, '>=')) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $message = "Ihre {$sqlFork}-Version '{$sqlVersion}' ist unzureichend f&uuml;r die Installation dieser Software. {$sqlFork}-Version {$neededSqlVersion} oder h&ouml;her wird ben&ouml;tigt.";
            } else {
                $message = "Your {$sqlFork} version '{$sqlVersion}' is insufficient for installation of this software. {$sqlFork} version {$neededSqlVersion} or greater is required.";
            }

            $issues[] = $message;
        }

        if (
            \extension_loaded('imagick')
            && !\in_array('WEBP', \Imagick::queryFormats())
        ) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $message = "Unterstützung für WebP-Grafiken in Imagick fehlt";
            } else {
                $message = "Support for WebP images in Imagick missing";
            }

            $issues[] = $message;
        }

        if (
            \extension_loaded('gd')
            && empty(\gd_info()['WebP Support'])
        ) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $message = "Unterstützung für WebP-Grafiken in GD fehlt";
            } else {
                $message = "Support for WebP images in GD missing";
            }

            $issues[] = $message;
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
            RegistryHandler::getInstance()->set('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride", \TIME_NOW);
        } else {
            RegistryHandler::getInstance()->delete('com.woltlab.wcf', PackageUpdateServer::class . "\0upgradeOverride");
        }

        PackageUpdateServer::resetAll();

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

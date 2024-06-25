<?php

namespace wcf\acp\form;

use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
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
                MultilineTextFormField::create('ckeditor5-license')
                    ->immutable()
                    ->label('CKEditor 5 FREE FOR OPEN SOURCE LICENSE AGREEMENT')
                    ->value('THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL CKSOURCE OR ITS LICENSORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.'),
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
            $this->checkPhpX64(),
            $this->checkMinimumDatabaseVersion(),
            $this->checkMysqlNativeDriver(),
            $this->checkForAppsWithDifferentDomains(),
            $this->checkCacheSourceIsNotMemcached(),
            $this->checkAttachmentStorage(),
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

    private function checkPhpX64(): ?array
    {
        if (\PHP_INT_SIZE === 8) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Fehlende Unterstützung für 64-Bit Werte',
                'description' => 'Die eingesetzte PHP-Version wurde ohne die Unterstützung von 64-Bit Werten erstellt.',
            ];
        } else {
            return [
                'title' => 'Missing support for 64-bit values',
                'description' => 'The PHP version being used was created without support for 64-bit values.',
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

    private function checkMysqlNativeDriver(): ?array
    {
        $sql = "SELECT 1";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();

        if ($statement->fetchSingleColumn() === 1) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Inkompatibler Treiber für den Datenbank-Zugriff',
                'description' => 'Für den Zugriff auf die Datenbank wird der moderne „MySQL Native Driver“ benötigt.',
            ];
        } else {
            return [
                'title' => 'Incompatible driver for the database access',
                'description' => 'The access to the database requires the modern “MySQL Native Driver”.',
            ];
        }
    }

    private function checkForAppsWithDifferentDomains(): ?array
    {
        $usesDifferentDomains = false;
        $domainName = '';
        foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
            if ($domainName === '') {
                $domainName = $application->domainName;
                continue;
            }

            if ($domainName !== $application->domainName) {
                $usesDifferentDomains = true;
                break;
            }
        }

        if (!$usesDifferentDomains) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Nutzung mehrerer Domains',
                'description' => 'Der Betrieb von Apps auf unterschiedlichen (Sub-)Domains wird nicht mehr unterstützt.',
            ];
        } else {
            return [
                'title' => 'Using multiple domains',
                'description' => 'The support for apps running on different (sub)domains has been discontinued.',
            ];
        }
    }

    private function checkCacheSourceIsNotMemcached(): ?array
    {
        if (\CACHE_SOURCE_TYPE !== 'memcached') {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Eingestellte Unterstützung für Memcached',
                'description' => 'Memcached wird nicht mehr unterstützt, als Alternative bietet sich die Nutzung von „Redis“ an.',
            ];
        } else {
            return [
                'title' => 'Discountinued support for Memcached',
                'description' => 'Memcached is no longer supported, it is recommended to switch to an alternative like “Redis”.',
            ];
        }
    }

    private function checkAttachmentStorage(): ?array
    {

        if (!\defined('ATTACHMENT_STORAGE') || !ATTACHMENT_STORAGE) {
            return null;
        }

        if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
            return [
                'title' => 'Alternativer Speicherort für Dateianhänge',
                'description' => \sprintf(
                    "Die Unterst&uuml;tzung f&uuml;r einen alternativen Speicherort von Dateianh&auml;ngen wird mit dem Upgrade entfernt. Es ist notwendig die Dateianh&auml;nge in das Standardverzeichnis '%s' zu verschieben und anschlie&szlig;end die PHP-Konstante 'ATTACHMENT_STORAGE' zu entfernen.",
                    WCF_DIR . 'attachments/',
                ),
            ];
        } else {
            return [
                'title' => 'Alternative storage location for attachments',
                'description' => \sprintf(
                    "The support for an alternative attachment storage location will be removed during the upgrade. It is required to move the attachments into the default directory '%s' and then to remove the PHP constant 'ATTACHMENT_STORAGE'.",
                    WCF_DIR . 'attachments/',
                ),
            ];
        }
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

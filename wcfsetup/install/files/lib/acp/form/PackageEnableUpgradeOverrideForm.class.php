<?php

namespace wcf\acp\form;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\RejectEverythingFormField;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\registry\RegistryHandler;
use wcf\system\search\SearchIndexManager;
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

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
        $tableNames = [];
        foreach ($objectTypes as $objectType) {
            $tableNames[] = SearchIndexManager::getTableName($objectType->objectType);
        }
        $conditionBuilder = new PreparedStatementConditionBuilder(true);
        $conditionBuilder->add('TABLE_NAME IN (?)', [$tableNames]);
        $conditionBuilder->add('TABLE_SCHEMA = ?', [WCF::getDB()->getDatabaseName()]);
        $conditionBuilder->add('ENGINE <> ?', ['InnoDB']);

        $sql = "SELECT  COUNT(*)
                FROM    INFORMATION_SCHEMA.TABLES
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());
        $nonInnoDbSearch = $statement->fetchSingleColumn() > 0;

        if ($nonInnoDbSearch) {
            if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
                $title = "Umstellung auf InnoDB-Suchindex";
                $description = "Es wurden noch nicht alle Tabellen auf InnoDB migriert.";
            } else {
                $title = "Migration to InnoDB Search Index";
                $description = "Not all tables have been migrated to InnoDB yet.";
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

<?php

namespace wcf\acp\form;

use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the form for adding new template groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateGroupAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.template.group.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.template.canManageTemplate'];

    /**
     * template group name
     * @var string
     */
    public $templateGroupName = '';

    /**
     * template group folder
     * @var int
     */
    public $templateGroupFolderName = '';

    /**
     * parent template group id
     * @var int
     */
    public $parentTemplateGroupID = 0;

    /**
     * available template groups
     * @var array
     */
    public $availableTemplateGroups = [];

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['templateGroupName'])) {
            $this->templateGroupName = StringUtil::trim($_POST['templateGroupName']);
        }
        if (!empty($_POST['templateGroupFolderName'])) {
            $this->templateGroupFolderName = StringUtil::trim($_POST['templateGroupFolderName']);
            if ($this->templateGroupFolderName) {
                $this->templateGroupFolderName = FileUtil::addTrailingSlash($this->templateGroupFolderName);
            }
        }
        if (isset($_POST['parentTemplateGroupID'])) {
            $this->parentTemplateGroupID = \intval($_POST['parentTemplateGroupID']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        $this->validateName();
        $this->validateFolderName();

        if ($this->parentTemplateGroupID && !isset($this->availableTemplateGroups[$this->parentTemplateGroupID])) {
            throw new UserInputException('parentTemplateGroupID', 'invalid');
        }
    }

    /**
     * Validates the template group name.
     */
    protected function validateName()
    {
        if (empty($this->templateGroupName)) {
            throw new UserInputException('templateGroupName');
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_template_group
                WHERE   templateGroupName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->templateGroupName]);

        if ($statement->fetchSingleColumn()) {
            throw new UserInputException('templateGroupName', 'notUnique');
        }
    }

    /**
     * Validates the template group folder name.
     */
    protected function validateFolderName()
    {
        if (empty($this->templateGroupFolderName)) {
            throw new UserInputException('templateGroupFolderName');
        }

        if (!\preg_match('/^[a-z0-9_\- ]+\/$/i', $this->templateGroupFolderName)) {
            throw new UserInputException('templateGroupFolderName', 'invalid');
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_template_group
                WHERE   templateGroupFolderName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->templateGroupFolderName]);

        if ($statement->fetchSingleColumn()) {
            throw new UserInputException('templateGroupFolderName', 'notUnique');
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $this->objectAction = new TemplateGroupAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'templateGroupName' => $this->templateGroupName,
                'templateGroupFolderName' => $this->templateGroupFolderName,
                'parentTemplateGroupID' => $this->parentTemplateGroupID ?: null,
            ]),
        ]);
        $returnValues = $this->objectAction->executeAction();
        $this->saved();

        // reset values
        $this->templateGroupName = $this->templateGroupFolderName = '';
        $this->parentTemplateGroupID = 0;

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                TemplateGroupEditForm::class,
                ['id' => $returnValues['returnValues']->templateGroupID]
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $this->availableTemplateGroups = TemplateGroup::getSelectList([-1], 1);

        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'templateGroupName' => $this->templateGroupName,
            'templateGroupFolderName' => $this->templateGroupFolderName,
            'parentTemplateGroupID' => $this->parentTemplateGroupID,
            'availableTemplateGroups' => $this->availableTemplateGroups,
        ]);
    }
}

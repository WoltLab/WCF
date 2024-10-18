<?php

namespace wcf\data\template\group;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\data\template\TemplateList;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes template group-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  TemplateGroup       create()
 * @method  TemplateGroupEditor[]   getObjects()
 * @method  TemplateGroupEditor getSingleObject()
 */
class TemplateGroupAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = TemplateGroupEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['copy', 'create', 'delete', 'update'];

    /**
     * @var TemplateGroupEditor
     */
    public $templateGroupEditor;

    /**
     * Validates the parameters to copy an existing template group.
     *
     * @throws      UserInputException
     */
    public function validateCopy()
    {
        WCF::getSession()->checkPermissions(['admin.template.canManageTemplate']);

        $this->readString('templateGroupName');
        $this->readString('templateGroupFolderName');

        $this->templateGroupEditor = $this->getSingleObject();

        // validate name
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_template_group
                WHERE   templateGroupName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->parameters['templateGroupName']]);

        if ($statement->fetchSingleColumn()) {
            throw new UserInputException('templateGroupName', 'notUnique');
        }

        // validate folder name
        if (!\preg_match('/^[a-z0-9_\- ]+\/$/i', $this->parameters['templateGroupFolderName'])) {
            throw new UserInputException('templateGroupFolderName', 'invalid');
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_template_group
                WHERE   templateGroupFolderName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->parameters['templateGroupFolderName']]);

        if ($statement->fetchSingleColumn()) {
            throw new UserInputException('templateGroupFolderName', 'notUnique');
        }
    }

    /**
     * Copies an existing template group.
     *
     * @return      string[]
     */
    public function copy()
    {
        // create a new template group
        $returnValues = (new self([], 'create', [
            'data' => [
                'parentTemplateGroupID' => ($this->templateGroupEditor->parentTemplateGroupID ?: null),
                'templateGroupName' => $this->parameters['templateGroupName'],
                'templateGroupFolderName' => $this->parameters['templateGroupFolderName'],
            ],
        ]))->executeAction();
        /** @var TemplateGroup $templateGroup */
        $templateGroup = $returnValues['returnValues'];

        // copy over the templates
        $templateList = new TemplateList();
        $templateList->getConditionBuilder()->add(
            "template.templateGroupID = ?",
            [$this->templateGroupEditor->templateGroupID]
        );
        $templateList->readObjects();

        /** @var Template $template */
        foreach ($templateList as $template) {
            (new TemplateAction([], 'create', [
                'data' => [
                    'application' => $template->application,
                    'templateName' => $template->templateName,
                    'packageID' => $template->packageID,
                    'templateGroupID' => $templateGroup->templateGroupID,
                ],
                'source' => $template->getSource(),
            ]))->executeAction();
        }

        return [
            'redirectURL' => LinkHandler::getInstance()->getLink(
                'TemplateGroupEdit',
                [
                    'isACP' => true,
                    'id' => $templateGroup->templateGroupID,
                ]
            ),
        ];
    }
}

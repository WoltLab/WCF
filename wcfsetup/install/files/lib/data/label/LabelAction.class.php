<?php

namespace wcf\data\label;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\language\item\LanguageItemAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\label\LabelHandler;
use wcf\system\WCF;

/**
 * Executes label-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LabelEditor[]   getObjects()
 * @method  LabelEditor getSingleObject()
 */
class LabelAction extends AbstractDatabaseObjectAction implements ISortableAction
{
    /**
     * @inheritDoc
     */
    protected $className = LabelEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update', 'updatePosition'];

    /**
     * @inheritDoc
     * @return  Label
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        /** @var Label $label */
        $label = parent::create();

        (new LabelEditor($label))->setShowOrder($label->groupID, $showOrder);

        return $label;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        // update showOrder if required
        if (
            \count($this->objects) === 1
            && isset($this->parameters['data']['groupID'])
            && isset($this->parameters['data']['showOrder'])
        ) {
            $labelEditor = $this->getObjects()[0];
            if ($labelEditor->groupID != $this->parameters['data']['groupID'] || $labelEditor->showOrder != $this->parameters['data']['showOrder']) {
                $labelEditor->setShowOrder(
                    $this->parameters['data']['groupID'],
                    $this->parameters['data']['showOrder']
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        parent::delete();

        if (!empty($this->objects)) {
            // identify i18n labels
            $languageVariables = [];
            foreach ($this->getObjects() as $object) {
                if (\preg_match('~wcf.acp.label.label\d+~', $object->label)) {
                    $languageVariables[] = $object->label;
                }
            }

            // remove language variables
            if (!empty($languageVariables)) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("languageItem IN (?)", [$languageVariables]);

                $sql = "SELECT  languageItemID
                        FROM    wcf1_language_item
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                $languageItemIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                $objectAction = new LanguageItemAction($languageItemIDs, 'delete');
                $objectAction->executeAction();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions(['admin.content.label.canManageLabel']);

        if (!isset($this->parameters['data']) || !isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        if (\count($this->parameters['data']['structure']) !== 1) {
            throw new UserInputException('structure');
        }

        $labelGroupID = \key($this->parameters['data']['structure']);
        $labelGroup = LabelHandler::getInstance()->getLabelGroup($labelGroupID);
        if ($labelGroup === null) {
            throw new UserInputException('structure');
        }

        $labelIDs = $this->parameters['data']['structure'][$labelGroupID];

        if (!empty(\array_diff($labelIDs, $labelGroup->getLabelIDs()))) {
            throw new UserInputException('structure');
        }

        $this->readInteger('offset', true, 'data');
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_label
                SET     showOrder = ?
                WHERE   labelID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $showOrder = $this->parameters['data']['offset'];

        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'] as $labelIDs) {
            foreach ($labelIDs as $labelID) {
                $statement->execute([
                    $showOrder++,
                    $labelID,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }
}

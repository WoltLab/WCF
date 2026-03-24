<?php

namespace wcf\data\label;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISortableAction;
use wcf\data\TI18nDatabaseObjectAction;
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
 * @extends AbstractDatabaseObjectAction<Label, LabelEditor>
 */
class LabelAction extends AbstractDatabaseObjectAction implements ISortableAction
{
    use TI18nDatabaseObjectAction;

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
     * @return  Label
     */
    #[\Override]
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }
        // `label` column doesn't have a default value
        $this->parameters['data']['label'] = $this->parameters['data']['label'] ?? '';

        /** @var Label $label */
        $label = parent::create();

        $this->saveI18nValue($label);

        (new LabelEditor($label))->setShowOrder($label->groupID, $showOrder);

        return $label;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $labelEditor) {
            $this->saveI18nValue($labelEditor->getDecoratedObject());
        }

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

    #[\Override]
    public function delete()
    {
        $count = parent::delete();

        $this->deleteI18nValues();

        return $count;
    }

    #[\Override]
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

    #[\Override]
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

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return ['label' => 'wcf.acp.label.label\d+'];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.acp.label';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return PACKAGE_ID;
    }
}

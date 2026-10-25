<?php

namespace wcf\system\importer;

use wcf\data\poll\option\PollOption;
use wcf\data\poll\option\PollOptionBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Imports poll votes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Only supports the legacy polls that are bound to a content object.
 */
class AbstractPollOptionImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = PollOption::class;

    /**
     * option object type name
     * @var string
     */
    protected $objectTypeName = '';

    /**
     * poll object type name
     * @var string
     */
    protected $pollObjectTypeName = '';

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['pollID'] = ImportHandler::getInstance()->getNewID($this->pollObjectTypeName, $data['pollID']);
        if ($data['pollID'] === null) {
            return 0;
        }

        $builder = PollOptionBuilder::forCreate()
            ->setPollID($data['pollID'])
            ->setOptionValue([L10nStorage::MONOLINGUAL => (string)($data['optionValue'] ?? '')]);
        unset($data['pollID'], $data['optionValue']);

        foreach ($data as $key => $value) {
            match ($key) {
                'votes' => $builder->setVotes($value),
                'showOrder' => $builder->setShowOrder($value),
                default => $builder->setCustomProperty($key, $value),
            };
        }

        $option = $builder->create();

        ImportHandler::getInstance()->saveNewID($this->objectTypeName, $oldID, $option->optionID);

        return $option->optionID;
    }
}

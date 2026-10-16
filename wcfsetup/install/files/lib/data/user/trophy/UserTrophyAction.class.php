<?php

namespace wcf\data\user\trophy;

use wcf\command\user\trophy\CreateUserTrophy;
use wcf\command\user\trophy\DeleteUserTrophies;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\l10n\L10nStorage;

/**
 * Provides user trophy actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `UserTrophyBuilder` and commands instead.
 *
 * @extends AbstractDatabaseObjectAction<UserTrophy, UserTrophyEditor>
 */
class UserTrophyAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.trophy.canAwardTrophy'];

    /**
     * @deprecated 6.3 use the `CreateUserTrophy` command instead.
     */
    #[\Override]
    public function create()
    {
        $data = $this->parameters['data'] ?? [];

        $builder = UserTrophyBuilder::forCreate()
            ->setTrophyID((int)$data['trophyID'])
            ->setUserID((int)$data['userID'])
            ->setTime((int)($data['time'] ?? \TIME_NOW))
            ->setUseCustomDescription((bool)($data['useCustomDescription'] ?? false))
            ->setTrophyUseHtml((bool)($data['trophyUseHtml'] ?? false));

        if (isset($data['description']) && $data['description'] !== '') {
            $builder->setDescription([L10nStorage::MONOLINGUAL => (string)$data['description']]);
        }

        $handledColumns = ['userTrophyID', 'trophyID', 'userID', 'time', 'useCustomDescription', 'trophyUseHtml', 'description'];
        foreach ($data as $key => $value) {
            if (!\in_array($key, $handledColumns, true)) {
                $builder->setCustomProperty($key, $value);
            }
        }

        return new CreateUserTrophy($builder)();
    }

    #[\Override]
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->objects as $object) {
            if ($object->getTrophy()->awardAutomatically !== 0) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @deprecated 6.3 use the `DeleteUserTrophies` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        new DeleteUserTrophies(\array_map(
            static fn(UserTrophyEditor $editor) => $editor->getDecoratedObject(),
            $this->objects
        ))();

        return \count($this->objects);
    }
}

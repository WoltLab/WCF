<?php

namespace wcf\system\user\content\provider;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectEditor;
use wcf\data\DatabaseObjectList;
use wcf\data\user\User;
use wcf\system\exception\ImplementationException;

/**
 * Abstract implementation for database user content provider.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @template T of DatabaseObjectList
 * @implements IUserContentProvider<T>
 */
abstract class AbstractDatabaseUserContentProvider implements IUserContentProvider
{
    /**
     * Returns the database object class for the object.
     *
     * @return string
     */
    abstract public static function getDatabaseObjectClass();

    /**
     * Returns the database object list class for the object.
     *
     * @return string
     */
    public static function getDatabaseObjectListClass()
    {
        return static::getDatabaseObjectClass() . 'List';
    }

    /**
     * Returns the database object action class for the object.
     *
     * @return string
     */
    public static function getDatabaseObjectActionClass()
    {
        return static::getDatabaseObjectClass() . 'Action';
    }

    #[\Override]
    public function getContentListForUser(User $user)
    {
        if ($user->userID == 0) {
            throw new \RuntimeException('Removing content for guests is not allowed.');
        }

        $className = static::getDatabaseObjectListClass();

        if (!\is_subclass_of($className, DatabaseObjectList::class)) {
            throw new ImplementationException($className, DatabaseObjectList::class);
        }

        /** @var T $databaseObjectList */
        $databaseObjectList = new $className();
        $tableAlias = \call_user_func([static::getDatabaseObjectClass(), 'getDatabaseTableAlias']);
        $databaseObjectList->getConditionBuilder()->add($tableAlias . '.userID = ?', [$user->userID]);

        return $databaseObjectList;
    }

    #[\Override]
    public function deleteContent(array $objectIDs)
    {
        $className = self::getDatabaseObjectActionClass();

        if (!\is_subclass_of($className, AbstractDatabaseObjectAction::class)) {
            throw new ImplementationException($className, AbstractDatabaseObjectAction::class);
        }

        /**
         * @var AbstractDatabaseObjectAction<DatabaseObject, DatabaseObjectEditor<DatabaseObject>> $objectAction
         */
        $objectAction = new $className($objectIDs, 'delete');
        $objectAction->executeAction();
    }
}

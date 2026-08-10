<?php

namespace wcf\data;

use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Default implementation for DatabaseObject-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of DatabaseObject
 * @template TDatabaseObjectEditor of DatabaseObjectEditor<TDatabaseObject>
 */
abstract class AbstractDatabaseObjectAction implements IDatabaseObjectAction, IDeleteAction
{
    /**
     * pending action
     * @var string
     */
    protected $action = '';

    /**
     * object editor class name
     * @var string
     */
    protected $className = '';

    /**
     * list of object ids
     * @var int[]
     */
    protected $objectIDs = [];

    /**
     * list of object editors
     * @var TDatabaseObjectEditor[]
     */
    protected $objects = [];

    /**
     * multi-dimensional array of parameters required by an action
     * @var mixed[]
     */
    protected $parameters = [];

    /**
     * list of permissions required to create objects
     * @var string[]
     */
    protected $permissionsCreate = [];

    /**
     * list of permissions required to delete objects
     * @var string[]
     */
    protected $permissionsDelete = [];

    /**
     * list of permissions required to update objects
     * @var string[]
     */
    protected $permissionsUpdate = [];

    /**
     * disallow requests for specified methods if the origin is not the ACP
     * @var string[]
     */
    protected $requireACP = [];

    /**
     * Resets cache if any of the listed actions is invoked
     * @var string[]
     */
    protected $resetCache = ['create', 'delete', 'toggle', 'update', 'updatePosition'];

    /**
     * values returned by executed action
     * @var mixed|mixed[]
     */
    protected $returnValues;

    /**
     * allows guest access for all specified methods, by default guest access
     * is completely disabled
     * @var string[]
     */
    protected $allowGuestAccess = [];

    const TYPE_INTEGER = 1;

    const TYPE_STRING = 2;

    const TYPE_BOOLEAN = 3;

    const TYPE_JSON = 4;

    const STRUCT_FLAT = 1;

    const STRUCT_ARRAY = 2;

    /**
     * Initialize a new DatabaseObject-related action.
     *
     * @param mixed[] $objects
     * @param mixed[] $parameters
     * @throws SystemException
     */
    public function __construct(array $objects, string $action, array $parameters = [])
    {
        // set class name
        if (empty($this->className)) {
            $className = static::class;

            if (\mb_substr($className, -6) === 'Action') {
                $this->className = \mb_substr($className, 0, -6) . 'Editor';
            }
        }

        $indexName = \call_user_func([$this->className, 'getDatabaseTableIndexName']);
        $baseClass = \call_user_func([$this->className, 'getBaseClass']);

        foreach ($objects as $object) {
            if (\is_object($object)) {
                if ($object instanceof $this->className) {
                    $this->objects[] = $object;
                } elseif ($object instanceof $baseClass) {
                    $this->objects[] = new $this->className($object);
                } else {
                    throw new SystemException('invalid value of parameter objects given');
                }

                $this->objectIDs[] = $object->{$indexName};
            } else {
                $this->objectIDs[] = $object;
            }
        }

        $this->action = $action;
        $this->parameters = $parameters;

        // initialize further settings
        $this->__init($baseClass, $indexName);

        // fire event action
        EventHandler::getInstance()->fireAction($this, 'initializeAction');
    }

    /**
     * This function can be overridden in children to perform custom initialization
     * of a DBOAction before the 'initializeAction' event is fired.
     *
     * @return void
     */
    protected function __init(string $baseClass, string $indexName)
    {
        // does nothing
    }

    #[\Override]
    public function validateAction()
    {
        // validate if user is logged in
        if (WCF::getUser()->isGuest() && !\in_array($this->getActionName(), $this->allowGuestAccess)) {
            throw new PermissionDeniedException();
        } elseif (
            !RequestHandler::getInstance()->isACPRequest() && \in_array(
                $this->getActionName(),
                $this->requireACP
            )
        ) {
            // attempt to invoke method, but origin is not the ACP
            throw new PermissionDeniedException();
        }

        // validate action name
        if (!\method_exists($this, $this->getActionName())) {
            throw new SystemException(\sprintf(
                "unknown action '%s' for '%s'",
                $this->getActionName(),
                $this::class,
            ));
        }

        $actionName = 'validate' . StringUtil::firstCharToUpperCase($this->getActionName());
        if (!\method_exists($this, $actionName)) {
            throw new PermissionDeniedException();
        }

        // validate action
        $this->{$actionName}();

        // fire event action
        EventHandler::getInstance()->fireAction($this, 'validateAction');
    }

    #[\Override]
    public function executeAction()
    {
        // execute action
        if (!\method_exists($this, $this->getActionName())) {
            throw new SystemException("call to undefined function '" . $this->getActionName() . "'");
        }

        $this->returnValues = $this->{$this->getActionName()}();

        // reset cache
        if (\in_array($this->getActionName(), $this->resetCache)) {
            $this->resetCache();
        }

        // fire event action
        EventHandler::getInstance()->fireAction($this, 'finalizeAction');

        return $this->getReturnValues();
    }

    /**
     * Resets cache of database object.
     *
     * @return void
     */
    protected function resetCache()
    {
        if (\is_subclass_of($this->className, IEditableCachedObject::class)) {
            \call_user_func([$this->className, 'resetCache']);
        }
    }

    #[\Override]
    public function getActionName()
    {
        return $this->action;
    }

    #[\Override]
    public function getObjectIDs()
    {
        return $this->objectIDs;
    }

    /**
     * Sets the database objects.
     *
     * @param TDatabaseObjectEditor[] $objects
     * @return void
     */
    public function setObjects(array $objects)
    {
        $this->objects = $objects;

        // update object IDs
        $this->objectIDs = [];
        foreach ($this->getObjects() as $object) {
            $this->objectIDs[] = $object->getObjectID();
        }
    }

    #[\Override]
    public function getParameters()
    {
        return $this->parameters;
    }

    #[\Override]
    public function getReturnValues()
    {
        return [
            'actionName' => $this->action,
            'objectIDs' => $this->getObjectIDs(),
            'returnValues' => $this->returnValues,
        ];
    }

    /**
     * Validates permissions and parameters.
     *
     * @return void
     */
    public function validateCreate()
    {
        // validate permissions
        if ($this->permissionsCreate !== []) {
            WCF::getSession()->checkPermissions($this->permissionsCreate);
        } else {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    public function validateDelete()
    {
        // validate permissions
        if ($this->permissionsDelete !== []) {
            WCF::getSession()->checkPermissions($this->permissionsDelete);
        } else {
            throw new PermissionDeniedException();
        }

        // read objects
        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Validates permissions and parameters.
     *
     * @return void
     */
    public function validateUpdate()
    {
        // validate permissions
        if ($this->permissionsUpdate !== []) {
            WCF::getSession()->checkPermissions($this->permissionsUpdate);
        } else {
            throw new PermissionDeniedException();
        }

        // read objects
        if ($this->objects === []) {
            $this->readObjects();

            if ($this->objects === []) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Creates new database object.
     *
     * @return TDatabaseObject
     */
    public function create()
    {
        return \call_user_func([$this->className, 'create'], $this->parameters['data']);
    }

    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        // get ids
        $objectIDs = [];
        foreach ($this->getObjects() as $object) {
            $objectIDs[] = $object->getObjectID();
        }

        // execute action
        return \call_user_func([$this->className, 'deleteAll'], $objectIDs);
    }

    /**
     * Updates data.
     *
     * @return void
     */
    public function update()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        if (isset($this->parameters['data'])) {
            foreach ($this->getObjects() as $object) {
                $object->update($this->parameters['data']);
            }
        }

        if (isset($this->parameters['counters'])) {
            foreach ($this->getObjects() as $object) {
                $object->updateCounters($this->parameters['counters']);
            }
        }
    }

    /**
     * Reads data by data id.
     *
     * @return void
     */
    protected function readObjects()
    {
        if ($this->objectIDs === []) {
            return;
        }

        // get base class
        $baseClass = \call_user_func([$this->className, 'getBaseClass']);

        // get db information
        $tableName = \call_user_func([$this->className, 'getDatabaseTableName']);
        $indexName = \call_user_func([$this->className, 'getDatabaseTableIndexName']);

        // get objects
        $sql = "SELECT  *
                FROM    " . $tableName . "
                WHERE   " . $indexName . " IN (" . \str_repeat('?,', \count($this->objectIDs) - 1) . "?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->objectIDs);
        // @phpstan-ignore argument.templateType
        while ($object = $statement->fetchObject($baseClass)) {
            $this->objects[] = new $this->className($object);
        }
    }

    /**
     * Returns a single object and throws a UserInputException if no or more than one object is given.
     *
     * @return TDatabaseObjectEditor
     * @throws UserInputException
     */
    protected function getSingleObject()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        if (\count($this->objects) !== 1) {
            throw new UserInputException('objectIDs');
        }

        \reset($this->objects);

        return \current($this->objects);
    }

    /**
     * Reads an integer value and validates it.
     *
     * @return void
     */
    protected function readInteger(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_INTEGER, self::STRUCT_FLAT);
    }

    /**
     * Reads an integer array and validates it.
     *
     * @return void
     */
    protected function readIntegerArray(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_INTEGER, self::STRUCT_ARRAY);
    }

    /**
     * Reads a string value and validates it.
     *
     * @return void
     */
    protected function readString(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_STRING, self::STRUCT_FLAT);
    }

    /**
     * Reads a string array and validates it.
     *
     * @return void
     */
    protected function readStringArray(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_STRING, self::STRUCT_ARRAY);
    }

    /**
     * Reads a boolean value and validates it.
     *
     * @return void
     */
    protected function readBoolean(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_BOOLEAN, self::STRUCT_FLAT);
    }

    /**
     * Reads a json-encoded value and validates it.
     *
     * @return void
     */
    protected function readJSON(string $variableName, bool $allowEmpty = false, string $arrayIndex = '')
    {
        $this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_JSON, self::STRUCT_FLAT);
    }

    /**
     * Reads a value and validates it. If you set $allowEmpty to true, no exception will
     * be thrown if the variable evaluates to 0 (int) or '' (string). Furthermore the
     * variable will be always created with a sane value if it does not exist.
     *
     * @return void
     * @throws SystemException
     * @throws UserInputException
     */
    protected function readValue(string $variableName, bool $allowEmpty, string $arrayIndex, int $type, int $structure)
    {
        if ($arrayIndex !== '') {
            if (!isset($this->parameters[$arrayIndex])) {
                if ($allowEmpty) {
                    // Implicitly create the structure to permit implicitly defined values.
                    $this->parameters[$arrayIndex] = [];
                } else {
                    throw new SystemException("Corrupt parameters, index '" . $arrayIndex . "' is missing");
                }
            }

            $target = &$this->parameters[$arrayIndex];
        } else {
            $target = &$this->parameters;
        }

        switch ($type) {
            case self::TYPE_INTEGER:
                if (!isset($target[$variableName])) {
                    if ($allowEmpty) {
                        $target[$variableName] = ($structure === self::STRUCT_FLAT) ? 0 : [];
                    } else {
                        throw new UserInputException($variableName);
                    }
                } else {
                    if ($structure === self::STRUCT_FLAT) {
                        $target[$variableName] = \intval($target[$variableName]);
                        if (!$allowEmpty && $target[$variableName] === 0) {
                            throw new UserInputException($variableName);
                        }
                    } else {
                        $target[$variableName] = ArrayUtil::toIntegerArray($target[$variableName]);
                        if (!\is_array($target[$variableName])) {
                            throw new UserInputException($variableName);
                        }

                        for ($i = 0, $length = \count($target[$variableName]); $i < $length; $i++) {
                            if ($target[$variableName][$i] === 0) {
                                throw new UserInputException($variableName);
                            }
                        }
                    }
                }
                break;

            case self::TYPE_STRING:
                if (!isset($target[$variableName])) {
                    if ($allowEmpty) {
                        $target[$variableName] = ($structure === self::STRUCT_FLAT) ? '' : [];
                    } else {
                        throw new UserInputException($variableName);
                    }
                } else {
                    if ($structure === self::STRUCT_FLAT) {
                        if (!\is_string($target[$variableName])) {
                            throw new UserInputException($variableName);
                        }
                        $target[$variableName] = StringUtil::trim($target[$variableName]);
                        if (!$allowEmpty && $target[$variableName] === '') {
                            throw new UserInputException($variableName);
                        }
                    } else {
                        $target[$variableName] = ArrayUtil::trim($target[$variableName]);
                        if (!\is_array($target[$variableName])) {
                            throw new UserInputException($variableName);
                        }

                        for ($i = 0, $length = \count($target[$variableName]); $i < $length; $i++) {
                            if ($target[$variableName][$i] === '') {
                                throw new UserInputException($variableName);
                            }
                        }
                    }
                }
                break;

            case self::TYPE_BOOLEAN:
                if (!isset($target[$variableName])) {
                    if ($allowEmpty) {
                        $target[$variableName] = false;
                    } else {
                        throw new UserInputException($variableName);
                    }
                } else {
                    if (\is_numeric($target[$variableName])) {
                        $target[$variableName] = (bool)$target[$variableName];
                    } else {
                        $target[$variableName] = $target[$variableName] !== 'false';
                    }
                }
                break;

            case self::TYPE_JSON:
                if (!isset($target[$variableName])) {
                    if ($allowEmpty) {
                        $target[$variableName] = [];
                    } else {
                        throw new UserInputException($variableName);
                    }
                } else {
                    try {
                        $target[$variableName] = \json_decode($target[$variableName], true, flags: \JSON_THROW_ON_ERROR);
                    } catch (\JsonException) {
                        throw new UserInputException($variableName);
                    }

                    if (!$allowEmpty && empty($target[$variableName])) {
                        throw new UserInputException($variableName);
                    }
                }
                break;
        }
    }

    /**
     * Returns object class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns a list of currently loaded objects.
     *
     * @return TDatabaseObjectEditor[]
     */
    public function getObjects()
    {
        return $this->objects;
    }
}

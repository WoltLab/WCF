<?php

namespace wcf\system\message\embedded\object;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default interface of embedded object handler.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MessageEmbeddedObjectManager extends SingletonFactory
{
    /**
     * caches message to embedded object assignments
     * @var array
     */
    protected $messageEmbeddedObjects = [];

    /**
     * caches embedded objects
     * @var array
     */
    protected $embeddedObjects = [];

    /**
     * object type of the active message
     * @var int
     */
    protected $activeMessageObjectTypeID;

    /**
     * id of the active message
     * @var int
     */
    protected $activeMessageID;

    /**
     * language id of the active message
     * @var int
     */
    protected $activeMessageLanguageID;

    /**
     * list of embedded object handlers
     * @var array
     */
    protected $embeddedObjectHandlers;

    /**
     * content language id
     * @var int
     */
    protected $contentLanguageID;

    /**
     * local cache for bulk operations
     * @var mixed[][]
     */
    protected $bulkData = [
        'insert' => [],
        'remove' => [],
    ];

    /**
     * A list of previous active message settings used to restore
     * the internal state in case of nested message processing.
     */
    protected $activeMessageHistory = [];

    /**
     * Registers the embedded objects found in given message.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor instance holding embedded object data
     * @param bool $isBulk true for bulk operations
     * @return      bool                 true if at least one embedded object was found
     */
    public function registerObjects(HtmlInputProcessor $htmlInputProcessor, $isBulk = false)
    {
        $context = $htmlInputProcessor->getContext();

        $messageObjectType = $context['objectType'];
        $messageObjectTypeID = $context['objectTypeID'];
        $messageID = $context['objectID'];

        // delete existing assignments
        if ($isBulk) {
            if (!isset($this->bulkData['remove'][$messageObjectType])) {
                $this->bulkData['remove'][$messageObjectType] = [];
            }
            $this->bulkData['remove'][$messageObjectType][] = $messageID;
        } else {
            $this->removeObjects($messageObjectType, [$messageID]);
        }

        $statement = null;
        if (!$isBulk) {
            // prepare statement
            $sql = "INSERT INTO wcf1_message_embedded_object
                                (messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID)
                    VALUES      (?, ?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
        }

        $embeddedData = $htmlInputProcessor->getEmbeddedContent();
        $returnValue = false;

        /** @var IMessageEmbeddedObjectHandler $handler */
        foreach ($this->getEmbeddedObjectHandlers() as $handler) {
            $objectIDs = $handler->parse($htmlInputProcessor, $embeddedData);

            if (!empty($objectIDs)) {
                foreach ($objectIDs as $objectID) {
                    $parameters = [$messageObjectTypeID, $messageID, $handler->objectTypeID, $objectID];
                    if ($isBulk) {
                        $this->bulkData['insert'][] = $parameters;
                    } else {
                        $statement->execute($parameters);
                    }
                }

                $returnValue = true;
            }
        }

        if (!$isBulk) {
            WCF::getDB()->commitTransaction();
        }

        return $returnValue;
    }

    /**
     * Commits the bulk operation by performing all deletes and inserts
     * in one big transaction to save performance.
     */
    public function commitBulkOperation()
    {
        // delete existing data
        WCF::getDB()->beginTransaction();
        foreach ($this->bulkData['remove'] as $objectType => $objectIDs) {
            $this->removeObjects($objectType, $objectIDs);
        }
        WCF::getDB()->commitTransaction();

        // prepare statement
        $sql = "INSERT INTO wcf1_message_embedded_object
                            (messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID)
                VALUES      (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($this->bulkData['insert'] as $parameters) {
            $statement->execute($parameters);
        }
        WCF::getDB()->commitTransaction();

        // reset cache
        $this->bulkData = [
            'insert' => [],
            'remove' => [],
        ];
    }

    /**
     * Registers the embedded objects found in a message using the simplified syntax.
     *
     * @param string $messageObjectType object type identifier
     * @param int $messageID object id
     * @param int[][] $embeddedContent list of object ids for embedded objects by object type id
     * @return      bool         true if at least one embedded object was found
     */
    public function registerSimpleObjects($messageObjectType, $messageID, array $embeddedContent)
    {
        $messageObjectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType);

        // delete existing assignments
        $this->removeObjects($messageObjectType, [$messageID]);

        if (empty($embeddedContent)) {
            return false;
        }

        // prepare statement
        $sql = "INSERT INTO wcf1_message_embedded_object
                            (messageObjectTypeID, messageID, embeddedObjectTypeID, embeddedObjectID)
                VALUES      (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        // call embedded object handlers
        WCF::getDB()->beginTransaction();
        foreach ($embeddedContent as $objectTypeID => $objectIDs) {
            foreach ($objectIDs as $objectID) {
                $statement->execute([$messageObjectTypeID, $messageID, $objectTypeID, $objectID]);
            }
        }
        WCF::getDB()->commitTransaction();

        return true;
    }

    /**
     * Removes embedded object assignments for given messages.
     *
     * @param string $messageObjectType
     * @param int[] $messageIDs
     */
    public function removeObjects($messageObjectType, array $messageIDs)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            'messageObjectTypeID = ?',
            [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType)]
        );
        $conditionBuilder->add('messageID IN (?)', [$messageIDs]);

        $sql = "DELETE FROM wcf1_message_embedded_object
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
    }

    /**
     * Loads the embedded objects for given messages.
     *
     * @param string $messageObjectType
     * @param int[] $messageIDs
     * @param int $contentLanguageID
     * @throws  InvalidObjectTypeException
     */
    public function loadObjects($messageObjectType, array $messageIDs, $contentLanguageID = null)
    {
        $messageObjectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType);
        if ($messageObjectTypeID === null) {
            throw new InvalidObjectTypeException($messageObjectType, 'com.woltlab.wcf.message');
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('messageObjectTypeID = ?', [$messageObjectTypeID]);
        $conditionBuilder->add('messageID IN (?)', [$messageIDs]);

        // get object ids
        $sql = "SELECT  *
                FROM    wcf1_message_embedded_object
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());
        $embeddedObjects = [];
        while ($row = $statement->fetchArray()) {
            if (!isset($this->embeddedObjects[$row['embeddedObjectTypeID']][$row['embeddedObjectID']])) {
                // group objects by object type
                if (!isset($embeddedObjects[$row['embeddedObjectTypeID']])) {
                    $embeddedObjects[$row['embeddedObjectTypeID']] = [];
                }
                $embeddedObjects[$row['embeddedObjectTypeID']][] = $row['embeddedObjectID'];
            }

            // store message to embedded object assignment
            if (!isset($this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']])) {
                $this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']] = [];
            }
            $this->messageEmbeddedObjects[$row['messageObjectTypeID']][$row['messageID']][$row['embeddedObjectTypeID']][] = $row['embeddedObjectID'];
        }

        $this->contentLanguageID = $contentLanguageID;

        // load objects
        foreach ($embeddedObjects as $embeddedObjectTypeID => $objectIDs) {
            if (!isset($this->embeddedObjects[$embeddedObjectTypeID])) {
                $this->embeddedObjects[$embeddedObjectTypeID] = [];
            }
            foreach ($this->getEmbeddedObjectHandler($embeddedObjectTypeID)->loadObjects(\array_unique($objectIDs)) as $objectID => $object) {
                $this->embeddedObjects[$embeddedObjectTypeID][$objectID] = $object;
            }
        }

        $this->contentLanguageID = null;
    }

    /**
     * Returns the content language id or null.
     *
     * @return      int
     */
    public function getContentLanguageID()
    {
        return $this->contentLanguageID;
    }

    /**
     * Sets active message information.
     *
     * @param string $messageObjectType
     * @param int $messageID
     * @param int $languageID
     */
    public function setActiveMessage($messageObjectType, $messageID, $languageID = null)
    {
        if ($this->activeMessageObjectTypeID) {
            $this->activeMessageHistory[] = [
                'activeMessageID' => $this->activeMessageID,
                'activeMessageLanguageID' => $this->activeMessageLanguageID,
                'activeMessageObjectTypeID' => $this->activeMessageObjectTypeID,
            ];
        }

        $this->activeMessageObjectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.message', $messageObjectType);
        $this->activeMessageID = $messageID;
        $this->activeMessageLanguageID = $languageID;
    }

    /**
     * Restores the internal state in case of nested message processing.
     */
    public function reset()
    {
        $newState = \array_pop($this->activeMessageHistory);
        if ($newState === null) {
            $newState = [
                'activeMessageID' => null,
                'activeMessageLanguageID' => null,
                'activeMessageObjectTypeID' => null,
            ];
        }

        $this->activeMessageID = $newState['activeMessageID'];
        $this->activeMessageLanguageID = $newState['activeMessageLanguageID'];
        $this->activeMessageObjectTypeID = $newState['activeMessageObjectTypeID'];
    }

    /**
     * Returns the language id of the active message.
     *
     * @return      int
     */
    public function getActiveMessageLanguageID()
    {
        return $this->activeMessageLanguageID;
    }

    /**
     * Returns all embedded objects of a specific type.
     *
     * @param string $embeddedObjectType
     * @return  array
     */
    public function getObjects($embeddedObjectType)
    {
        $embeddedObjectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', $embeddedObjectType);
        $returnValue = [];
        if (!empty($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID])) {
            foreach ($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID] as $embeddedObjectID) {
                if (isset($this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID])) {
                    $returnValue[] = $this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID];
                }
            }
        }

        return $returnValue;
    }

    /**
     * Returns a specific embedded object.
     *
     * @param string $embeddedObjectType
     * @param int $objectID
     * @return  \wcf\data\DatabaseObject|null
     */
    public function getObject($embeddedObjectType, $objectID)
    {
        // `$objectID` might contain non integer values containing a comment, such
        // as `123#This is a comment`. PHP <8.0 would silently truncate the value
        // similar to what `intval()` does, making the below comparison work.
        //
        // However, in PHP 8.0 it was decided that this behavior is intransparent
        // and somewhat violates the implicit casting rules, such a comparison
        // now yields `false` where it was previously true.
        //
        // See https://wiki.php.net/rfc/string_to_number_comparison
        $objectID = \intval($objectID);

        $embeddedObjectTypeID = ObjectTypeCache::getInstance()
            ->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', $embeddedObjectType);
        if (!empty($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID])) {
            foreach ($this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$embeddedObjectTypeID] as $embeddedObjectID) {
                if ($embeddedObjectID == $objectID) {
                    if (isset($this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID])) {
                        return $this->embeddedObjects[$embeddedObjectTypeID][$embeddedObjectID];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Temporarily registers a message, the parsed data will not be stored.
     *
     * @param HtmlInputProcessor $htmlInputProcessor html input processor
     */
    public function registerTemporaryMessage(HtmlInputProcessor $htmlInputProcessor)
    {
        $context = $htmlInputProcessor->getContext();

        // set active message information
        $this->activeMessageObjectTypeID = $context['objectTypeID'];
        $this->activeMessageID = $context['objectID'];

        $embeddedData = $htmlInputProcessor->getEmbeddedContent();

        /** @var IMessageEmbeddedObjectHandler $handler */
        foreach ($this->getEmbeddedObjectHandlers() as $handler) {
            $objectIDs = $handler->parse($htmlInputProcessor, $embeddedData);

            if (!empty($objectIDs)) {
                // save assignments
                $this->messageEmbeddedObjects[$this->activeMessageObjectTypeID][$this->activeMessageID][$handler->objectTypeID] = $objectIDs;

                // loads objects
                $this->embeddedObjects[$handler->objectTypeID] = $handler->loadObjects($objectIDs);
            }
        }
    }

    /**
     * @return      ISimpleMessageEmbeddedObjectHandler[];
     */
    public function getSimpleMessageEmbeddedObjectHandlers()
    {
        $handlers = [];
        foreach ($this->getEmbeddedObjectHandlers() as $handler) {
            if ($handler instanceof ISimpleMessageEmbeddedObjectHandler) {
                $name = \lcfirst(\preg_replace(
                    '~^.*\\\\([A-Z][a-zA-Z]+)MessageEmbeddedObjectHandler$~',
                    '$1',
                    \get_class($handler)
                ));
                $handlers[$name] = $handler;
            }
        }

        return $handlers;
    }

    /**
     * Returns all embedded object handlers.
     *
     * @return  IMessageEmbeddedObjectHandler[]
     */
    protected function getEmbeddedObjectHandlers()
    {
        if ($this->embeddedObjectHandlers === null) {
            $this->embeddedObjectHandlers = [];
            foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.message.embeddedObject') as $objectType) {
                $this->embeddedObjectHandlers[$objectType->objectTypeID] = $objectType->getProcessor();
            }
        }

        return $this->embeddedObjectHandlers;
    }

    /**
     * Returns a specific embedded object handler.
     *
     * @param int $objectTypeID
     * @return  IMessageEmbeddedObjectHandler
     */
    protected function getEmbeddedObjectHandler($objectTypeID)
    {
        $this->getEmbeddedObjectHandlers();

        return $this->embeddedObjectHandlers[$objectTypeID];
    }
}

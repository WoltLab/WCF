<?php

namespace wcf\system\search;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\Package;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\search\mysql\MysqlSearchIndexManager;
use wcf\system\SingletonFactory;

/**
 * Manages the search index.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SearchIndexManager extends SingletonFactory implements IContextAwareSearchIndexManager
{
    /**
     * list of available object types
     * @var array
     */
    protected $availableObjectTypes = [];

    /**
     * list of application packages
     * @var Package[]
     */
    protected static $packages = [];

    /**
     * search index manager object
     * @var ISearchIndexManager
     */
    protected $searchIndexManager;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get available object types
        $this->availableObjectTypes = ObjectTypeCache::getInstance()
            ->getObjectTypes('com.woltlab.wcf.searchableObjectType');
    }

    /**
     * Returns the id of the object type with the given name.
     *
     * @throws  SystemException
     */
    public function getObjectTypeID(string $objectType): int
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType]->objectTypeID;
    }

    /**
     * Returns the the object type with the given name.
     *
     * @throws  SystemException
     */
    public function getObjectType(string $objectType): ObjectType
    {
        if (!isset($this->availableObjectTypes[$objectType])) {
            throw new SystemException("unknown object type '" . $objectType . "'");
        }

        return $this->availableObjectTypes[$objectType];
    }

    /**
     * Returns the search index manager object.
     */
    protected function getSearchIndexManager(): ISearchIndexManager
    {
        if ($this->searchIndexManager === null) {
            $className = '';
            if (SEARCH_ENGINE != 'mysql') {
                $className = 'wcf\system\search\\' . SEARCH_ENGINE . '\\' . \ucfirst(SEARCH_ENGINE) . 'SearchIndexManager';
                if (!\class_exists($className)) {
                    $className = '';
                }
            }

            // fallback to MySQL
            if (empty($className)) {
                $className = MysqlSearchIndexManager::class;
            }

            $this->searchIndexManager = \call_user_func([$className, 'getInstance']);
        }

        return $this->searchIndexManager;
    }

    /**
     * @inheritDoc
     */
    public function set(
        $objectType,
        $objectID,
        $message,
        $subject,
        $time,
        $userID,
        $username,
        $languageID = null,
        $metaData = ''
    ): void {
        // Inserts a whitespace after certain tags to prevent words from adjacent
        // lines to be effectively be glued together when the tags are removed.
        $message = \preg_replace('~(<br>|</(?:h[1-6]|kbd|li|p|pre|td|woltlab-metacode)>)~', '\\1 ', $message);

        // Strip HTML tags.
        $dom = new \DOMDocument();
        @$dom->loadHTML(\sprintf(
            '<?xml version="1.0" encoding="UTF-8"?><html><body>%s</body></html>',
            $message
        ));
        $message = $dom->documentElement->textContent;

        $this->getSearchIndexManager()
            ->set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
    }

    /**
     * @inheritDoc
     * @since 6.0
     */
    public function setWithContext(
        string $objectType,
        int $objectID,
        int $parentID,
        int $containerID,
        string $message,
        string $subject,
        int $time,
        ?int $userID,
        string $username,
        ?int $languageID = null,
        string $metaData = ''
    ): void {
        $searchIndexManager = $this->getSearchIndexManager();
        if ($searchIndexManager instanceof IContextAwareSearchIndexManager) {
            // Inserts a whitespace after certain tags to prevent words from adjacent
            // lines to be effectively be glued together when the tags are removed.
            $message = \preg_replace('~(<br>|</(?:h[1-6]|kbd|li|p|pre|td|woltlab-metacode)>)~', '\\1 ', $message);

            // Strip HTML tags.
            $dom = new \DOMDocument();
            @$dom->loadHTML(\sprintf(
                '<?xml version="1.0" encoding="UTF-8"?><html><body>%s</body></html>',
                $message
            ));
            $message = $dom->documentElement->textContent;

            $searchIndexManager->setWithContext(
                $objectType,
                $objectID,
                $parentID,
                $containerID,
                $message,
                $subject,
                $time,
                $userID,
                $username,
                $languageID,
                $metaData
            );
        } else {
            $this
                ->set($objectType, $objectID, $message, $subject, $time, $userID, $username, $languageID, $metaData);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete($objectType, array $objectIDs): void
    {
        $this->getSearchIndexManager()->delete($objectType, $objectIDs);
    }

    /**
     * @inheritDoc
     */
    public function reset($objectType): void
    {
        $this->getSearchIndexManager()->reset($objectType);
    }

    /**
     * @inheritDoc
     */
    public function createSearchIndices(): void
    {
        $this->getSearchIndexManager()->createSearchIndices();
    }

    /**
     * @inheritDoc
     */
    public function beginBulkOperation(): void
    {
        $this->getSearchIndexManager()->beginBulkOperation();
    }

    /**
     * @inheritDoc
     */
    public function commitBulkOperation(): void
    {
        $this->getSearchIndexManager()->commitBulkOperation();
    }

    /**
     * Returns the database table name for the object type's search index.
     */
    public static function getTableName(ObjectType|string $objectType): string
    {
        if (\is_string($objectType)) {
            $objectType = self::getInstance()->getObjectType($objectType);
        }

        if ($objectType->searchindex) {
            $tableName = $objectType->searchindex;

            if (!empty($tableName)) {
                return ApplicationHandler::insertRealDatabaseTableNames($tableName, true);
            }
        }

        return 'wcf1_search_index_' . \substr(\sha1($objectType->objectType), 0, 8);
    }
}

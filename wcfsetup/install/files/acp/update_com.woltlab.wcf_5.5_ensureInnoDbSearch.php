<?php

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.searchableObjectType');
$tableNames = [];
foreach ($objectTypes as $objectType) {
    $tableNames[] = SearchIndexManager::getTableName($objectType->objectType);
}
$conditionBuilder = new PreparedStatementConditionBuilder(true);
$conditionBuilder->add('TABLE_NAME IN (?)', [$tableNames]);
$conditionBuilder->add('TABLE_SCHEMA = ?', [WCF::getDB()->getDatabaseName()]);
$conditionBuilder->add('ENGINE <> ?', ['InnoDB']);

$sql = "SELECT  COUNT(*)
        FROM    INFORMATION_SCHEMA.TABLES
        " . $conditionBuilder;
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute($conditionBuilder->getParameters());
$nonInnoDbSearch = $statement->fetchSingleColumn() > 0;

if ($nonInnoDbSearch) {
    if (WCF::getLanguage()->getFixedLanguageCode() === 'de') {
        $message = "Es wurden noch nicht alle Tabellen auf InnoDB migriert.";
    } else {
        $message = "The migration to InnoDB was not yet performed for all database tables.";
    }

    throw new \RuntimeException($message);
}

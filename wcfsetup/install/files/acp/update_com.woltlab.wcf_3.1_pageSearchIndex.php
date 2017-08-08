<?php
use wcf\system\cache\builder\ObjectTypeCacheBuilder;
use wcf\system\search\SearchIndexManager;

ObjectTypeCacheBuilder::getInstance()->reset();
SearchIndexManager::getInstance()->createSearchIndices();

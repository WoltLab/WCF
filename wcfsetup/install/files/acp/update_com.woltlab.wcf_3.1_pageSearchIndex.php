<?php
use wcf\system\cache\builder\ObjectTypeCacheBuilder;
use wcf\system\search\SearchIndexManager;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
ObjectTypeCacheBuilder::getInstance()->reset();
SearchIndexManager::getInstance()->createSearchIndices();

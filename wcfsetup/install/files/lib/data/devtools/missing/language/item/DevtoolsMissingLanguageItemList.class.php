<?php
namespace wcf\data\devtools\missing\language\item;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of missing language item log entries.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Devtools\Missing\Language\Item
 * @since	5.3
 * 
 * @method	DevtoolsMissingLanguageItem		current()
 * @method	DevtoolsMissingLanguageItem[]		getObjects()
 * @method	DevtoolsMissingLanguageItem		getSingleObject()
 * @method	DevtoolsMissingLanguageItem|null	search($objectID)
 * @property	DevtoolsMissingLanguageItem[]		$objects
 */
class DevtoolsMissingLanguageItemList extends DatabaseObjectList {}

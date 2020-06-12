<?php
namespace wcf\data\cronjob;
use wcf\data\I18nDatabaseObjectList;

/**
 * I18n implementation of cronjob list.
 *
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Cronjob
 * @since       5.3
 *
 * @method	Cronjob		current()
 * @method	Cronjob[]	getObjects()
 * @method	Cronjob|null	search($objectID)
 * @property	Cronjob[]	$objects
 */
class I18nCronjobList extends I18nDatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $i18nFields = ['description' => 'descriptionI18n'];
	
	/**
	 * @inheritDoc
	 */
	public $className = Cronjob::class;
}

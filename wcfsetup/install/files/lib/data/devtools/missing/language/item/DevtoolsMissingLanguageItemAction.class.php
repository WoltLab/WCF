<?php
namespace wcf\data\devtools\missing\language\item;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IDeleteAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\JSON;
use function wcf\functions\exception\sanitizeStacktrace;

/**
 * Executes missing language item log entry-related actions.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Devtools\Missing\Language\Item
 * @since	5.3
 * 
 * @method	DevtoolsMissingLanguageItemEditor[]	getObjects()
 * @method	DevtoolsMissingLanguageItemEditor	getSingleObject()
 */
class DevtoolsMissingLanguageItemAction extends AbstractDatabaseObjectAction implements IDeleteAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * Logs a missing language item.
	 */
	public function logLanguageItem() {
		$stackTraceData = sanitizeStacktrace(new \Exception(), true);
		// Remove stack entries related to logging.
		array_shift($stackTraceData);
		array_shift($stackTraceData);
		array_shift($stackTraceData);
		$stackTrace = JSON::encode($stackTraceData);
		
		$sql = "INSERT INTO		wcf" . WCF_N . "_devtools_missing_language_item
						(languageID, languageItem, lastTime, stackTrace)
			VALUES			(?, ?, ?, ?)
			ON DUPLICATE KEY
			UPDATE			lastTime = ?,
						stackTrace = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->parameters['language']->languageID,
			$this->parameters['languageItem'],
			TIME_NOW,
			$stackTrace,
			
			TIME_NOW,
			$stackTrace,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		if (!ENABLE_DEVELOPER_TOOLS || !LOG_MISSING_LANGUAGE_ITEMS) {
			throw new IllegalLinkException();
		}
		
		parent::validateDelete();
	}
	
	/**
	 * Validates the `clearLog` action.
	 */
	public function validateClearLog() {
		if (!ENABLE_DEVELOPER_TOOLS || !LOG_MISSING_LANGUAGE_ITEMS) {
			throw new IllegalLinkException();
		}
		
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
	}
	
	/**
	 * Removes all entries from the missing language item log.
	 */
	public function clearLog() {
		$sql = "DELETE FROM	wcf" . WCF_N . "_devtools_missing_language_item";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
	}
}

<?php
namespace wcf\data\devtools\missing\language\item;
use wcf\data\DatabaseObject;
use wcf\data\language\Language;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Represents a missing language item log entry.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Devtools\Missing\Language\Item
 * @since	5.3
 * 
 * @property-read	integer		$itemID		unique id of the missing language item log entry
 * @property-read	integer		$languageID	id of the language the missing language item was requested for
 * @property-read	string		$languageItem	name of the missing language item
 * @property-read	integer		$lastTime	timestamp of the last time the missing language item was requested
 * @property-read	string		$stackTrace	stack trace of how the missing language item was requested for the last time
 */
class DevtoolsMissingLanguageItem extends DatabaseObject {
	/**
	 * Returns the language the missing language item was requested for or `null` if the language
	 * does not exist anymore.
	 * 
	 * @return	null|Language
	 */
	public function getLanguage() {
		if ($this->languageID === null) {
			return null;
		}
		
		return LanguageFactory::getInstance()->getLanguage($this->languageID);
	}
	
	/**
	 * Returns the formatted stack trace of how the missing language item was requested for the
	 * last time.
	 * 
	 * @return	string
	 */
	public function getStackTrace() {
		$stackTrace = JSON::decode($this->stackTrace);
		foreach ($stackTrace as &$stackEntry) {
			foreach ($stackEntry['args'] as &$stackEntryArg) {
				if (gettype($stackEntryArg) === 'string') {
					$stackEntryArg = str_replace(["\n", "\t"], ['\n', '\t'], $stackEntryArg);
				}
			}
			unset($stackEntryArg);
		}
		unset($stackEntry);
		
		return WCF::getTPL()->fetch('__devtoolsMissingLanguageItemStackTrace', 'wcf', [
			'stackTrace' => $stackTrace,
		]);
	}
}

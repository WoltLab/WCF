<?php
namespace wcf\system\option;
use wcf\system\cache\builder\ContactOptionCacheBuilder;

/**
 * Option handler implementation for the contact form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class ContactOptionHandler extends CustomOptionHandler {
	/**
	 * @inheritDoc
	 */
	protected function readCache() {
		$this->cachedOptions = ContactOptionCacheBuilder::getInstance()->getData();
	}
}

<?php
namespace wcf\system\moderation;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a deleted content provider.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation
 * @category	Community Framework
 */
abstract class AbstractDeletedContentProvider extends AbstractObjectTypeProcessor implements IDeletedContentProvider {
	/**
	 * @see	\wcf\system\moderation\IDeletedContentProvider::getApplication()
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
}

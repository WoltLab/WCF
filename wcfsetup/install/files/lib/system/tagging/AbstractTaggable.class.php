<?php
namespace wcf\system\tagging;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a taggable.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.tagging
 * @category	Community Framework
 */
abstract class AbstractTaggable extends AbstractObjectTypeProcessor implements ITaggable {
	/**
	 * @see	\wcf\system\tagging\ITaggable::getApplication()
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
}

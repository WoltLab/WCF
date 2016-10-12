<?php
namespace wcf\system\tagging;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a taggable.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
abstract class AbstractTaggable extends AbstractObjectTypeProcessor implements ITaggable {
	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
}

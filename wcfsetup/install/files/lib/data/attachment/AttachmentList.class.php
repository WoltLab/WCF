<?php
namespace wcf\data\attachment;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.attachment
 * @subpackage	data.attachment
 * @category	Community Framework
 */
class AttachmentList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\attachment\Attachment';
}

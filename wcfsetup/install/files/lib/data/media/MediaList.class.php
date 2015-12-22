<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of madia files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 */
class MediaList extends DatabaseObjectList {
	/**
	 * @inheritdoc
	 */
	public $className = Media::class;
}

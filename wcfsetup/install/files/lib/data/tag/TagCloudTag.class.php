<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a tag in a tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2009-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category	Community Framework
 */
class TagCloudTag extends DatabaseObjectDecorator {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\tag\Tag';
	
	/**
	 * size of the tag in a weighted list
	 * @var	double
	 */
	protected $size = 0.0;
	
	/**
	 * Sets the size of the tag.
	 * 
	 * @param	double		$size
	 */
	public function setSize($size) {
		$this->size = $size;
	}
	
	/**
	 * Returns the size of the tag.
	 * 
	 * @return	double
	 */
	public function getSize() {
		return $this->size;
	}
}

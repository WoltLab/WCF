<?php
namespace wcf\data\tag;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a tag in a tag cloud.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Tag
 * 
 * @method	Tag	getDecoratedObject()
 * @mixin	Tag
 * 
 * @property-read	integer|null	$counter	number of the times the tag has been used for a certain object type or `null`
 */
class TagCloudTag extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Tag::class;
	
	/**
	 * weight of the tag in a weighted list
	 * @var	integer
	 */
	protected $weight = 1;
	
	/**
	 * Sets the weight of the tag.
	 *
	 * @param	double		$weight
	 */
	public function setWeight($weight) {
		$this->weight = $weight;
	}
	
	/**
	 * Returns the weight of the tag.
	 *
	 * @return	integer
	 */
	public function getWeight() {
		return $this->weight;
	}
		
	/**
	 * Sets the size of the tag.
	 * 
	 * @param	double		$size
	 * @deprecated  3.0
	 */
	public function setSize($size) {}
	
	/**
	 * Returns the size of the tag.
	 * 
	 * @return	double
	 * @deprecated  3.0
	 */
	public function getSize() {
		return (($this->weight - 1) / 6) * 85 + 85;
	}
}

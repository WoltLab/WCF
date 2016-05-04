<?php
namespace wcf\system\box;
use wcf\data\box\Box;

/**
 * Default interface for box controllers.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.box
 * @category	Community Framework
 * @since	2.2
 */
interface IBoxController {
	/**
	 * Returns the content of this box.
	 *
	 * @return	string
	 */
	public function getContent();
	
	/**
	 * Returns false if this box has no content.
	 *
	 * @return	boolean
	 */
	public function hasContent();
	
	/**
	 * Returns the image of this box.
	 *
	 * @return	\wcf\data\media\ViewableMedia
	 */
	public function getImage();
	
	/**
	 * Returns true if this box has an image.
	 *
	 * @return	boolean
	 */
	public function hasImage();
	
	/**
	 * Returns the title link of this box.
	 *
	 * @return	string
	 */
	public function getLink();
	
	/**
	 * Returns true if this box has a title link.
	 *
	 * @return	boolean
	 */
	public function hasLink();
	
	/**
	 * Returns the database object of this box.
	 * 
	 * @return      Box
	 */
	public function getBox();
	
	/**
	 * Sets the database object of this box.
	 *
	 * @param	Box	$box
	 */
	public function setBox(Box $box);
	
	/**
	 * Returns a list of supported box positions.
	 * 
	 * @return	string[]
	 */
	public function getSupportedPositions();
}

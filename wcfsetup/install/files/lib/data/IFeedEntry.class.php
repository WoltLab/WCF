<?php
namespace wcf\data;

/**
 * Every feed entry should implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IFeedEntry extends IMessage {
	/**
	 * Returns the number of comments.
	 * 
	 * @return	integer
	 */
	public function getComments();
	
	/**
	 * Returns a list of category names.
	 * 
	 * @return	array<string>
	 */
	public function getCategories();
}

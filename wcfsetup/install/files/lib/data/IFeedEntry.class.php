<?php
namespace wcf\data;

/**
 * Every feed entry should implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IFeedEntry extends IMessage {
	/**
	 * Returns the number of comments.
	 * 
	 * @return	int
	 */
	public function getComments();
	
	/**
	 * Returns a list of category names.
	 * 
	 * @return	string[]
	 */
	public function getCategories();
}

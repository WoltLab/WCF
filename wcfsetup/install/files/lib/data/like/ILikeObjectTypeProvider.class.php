<?php
namespace wcf\data\like;
use wcf\data\like\object\ILikeObject;
use wcf\data\object\type\IObjectTypeProvider;

/**
 * Default interface for like object type providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
interface ILikeObjectTypeProvider extends IObjectTypeProvider {
	/**
	 * Returns true if the active user can access the given likeable object.
	 * 
	 * @param	\wcf\data\like\object\ILikeObject	$object
	 * @return	boolean
	 */
	public function checkPermissions(ILikeObject $object);
}

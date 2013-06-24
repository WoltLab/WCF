<?php
namespace wcf\page;

/**
 * Represents a trackable page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
interface ITrackablePage {
	/**
	 * Returns true if this page should be tracked.
	 * 
	 * @return	boolean
	 */
	public function isTracked();
	
	/**
	 * Returns the controller name.
	 *
	 * @return	string
	 */
	public function getController();
	
	/**
	 * Returns the parent object type.
	 *
	 * @return	string
	 */
	public function getParentObjectType();
	
	/**
	 * Returns the parent object id.
	 *
	 * @return	integer
	 */
	public function getParentObjectID();
	
	/**
	 * Returns the object type.
	 *
	 * @return	string
	 */
	public function getObjectType();
	
	/**
	 * Returns the object id.
	 *
	 * @return	integer
	 */
	public function getObjectID();
}

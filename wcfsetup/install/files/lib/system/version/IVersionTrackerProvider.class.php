<?php
namespace wcf\system\version;
use wcf\data\object\type\IObjectTypeProvider;
use wcf\data\IVersionTrackerObject;

/**
 * Represents objects that support some of their properties to be saved.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Version
 * @since	3.1
 */
interface IVersionTrackerProvider extends IObjectTypeProvider {
	/**
	 * Returns true if current user can view the stored versions of this type.
	 * 
	 * @return      boolean
	 */
	public function canAccess();
	
	/**
	 * Returns the internal identifier for the ACP menu item that should be
	 * marked as active when navigating through the relevant versions.
	 * 
	 * @return      string          active menu item identifier
	 */
	public function getActiveMenuItem();
	
	/**
	 * Returns an arbitrary version entry that represents the current version
	 * 
	 * @param       IVersionTrackerObject   $object         target object
	 * @return      VersionTrackerEntry
	 */
	public function getCurrentVersion(IVersionTrackerObject $object);
	
	/**
	 * Returns the name of the default property used when initiating a diff.
	 * 
	 * @return      string          default property name
	 */
	public function getDefaultProperty();
	
	/**
	 * Returns the label for provided property.
	 * 
	 * @param       string          $property       property name
	 * @return      string          property label
	 */
	public function getPropertyLabel($property);
	
	/**
	 * Returns an array containing the values that should be stored in the database.
	 * 
	 * @param       IVersionTrackerObject   $object         target object
	 * @return      mixed[]                 property to value mapping
	 */
	public function getTrackedData(IVersionTrackerObject $object);
	
	/**
	 * Returns the list of tracked properties.
	 * 
	 * @return      string[]        list of tracked properties
	 */
	public function getTrackedProperties();
	
	/**
	 * Indicates that the payload is provided for each language and that the
	 * payload's array indices represent language ids rather than property values.
	 * 
	 * @param       IVersionTrackerObject   $object         target object
	 * @return      boolean
	 */
	public function isI18n(IVersionTrackerObject $object);
	
	/**
	 * Reverts an object to a previous version.
	 * 
	 * @param       IVersionTrackerObject   $object         target object
	 * @param       VersionTrackerEntry     $entry          previous version
	 */
	public function revert(IVersionTrackerObject $object, VersionTrackerEntry $entry);
}

<?php
namespace wcf\data\application;

/**
 * Represents a list of viewable applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ViewableApplicationList extends ApplicationList {
	/**
	 * @see	wcf\data\DatabaseObjectList::__construct()
	 */
	public function __construct() {
		parent::__construct();
		
		// exclude WCF pseudo-application
		$this->getConditionBuilder()->add("application.packageID <> ?", array(1));
	}
	
	/**
	 * @see	wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		foreach ($this->objects as &$application) {
			$application = new ViewableApplication($application);
		}
		unset($application);
	}
}

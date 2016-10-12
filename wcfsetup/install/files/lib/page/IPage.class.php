<?php
namespace wcf\page;

/**
 * All page classes should implement this interface. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
interface IPage {
	/**
	 * Initializes the page.
	 */
	public function __run();
	
	/**
	 * Reads the given parameters.
	 */
	public function readParameters();
	
	/**
	 * Checks the modules of this page.
	 */
	public function checkModules();
	
	/**
	 * Checks the permissions of this page.
	 */
	public function checkPermissions();
	
	/**
	 * Reads/Gets the data to be displayed on this page.
	 */
	public function readData();
	
	/**
	 * Assigns variables to the template engine.
	 */
	public function assignVariables();
	
	/**
	 * Shows the requested page.
	 */
	public function show();
}

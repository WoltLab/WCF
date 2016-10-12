<?php
namespace wcf\system\moderation;

/**
 * Interface for deleted content provider.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation
 */
interface IDeletedContentProvider {
	/**
	 * Returns a list of deleted content.
	 * 
	 * @return	\wcf\data\DatabaseObjectList
	 */
	public function getObjectList();
	
	/**
	 * Returns the template name for the result output.
	 * 
	 * @return	string
	 */
	public function getTemplateName();
	
	/**
	 * Returns the application of the result template.
	 * 
	 * @return	string
	 */
	public function getApplication();
}

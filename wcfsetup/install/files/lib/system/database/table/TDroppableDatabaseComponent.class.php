<?php
namespace wcf\system\database\table;

/**
 * Provides methods for database components which can be dropped.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table
 * @since	5.2
 */
trait TDroppableDatabaseComponent {
	/**
	 * is `true` if the component will be dropped
	 * @var	bool
	 */
	protected $drop = false;
	
	/**
	 * Marks the component to be dropped.
	 * 
	 * @return	$this
	 */
	public function drop() {
		$this->drop = true;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the component will be dropped.
	 * 
	 * @return	bool
	 */
	public function willBeDropped() {
		return $this->drop;
	}
}

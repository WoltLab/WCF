<?php
namespace wcf\data\application\group;
use wcf\data\application\Application;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides a viewable application group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category	Community Framework
 */
class ViewableApplicationGroup extends DatabaseObjectDecorator implements \Countable, \Iterator {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\application\group\ApplicationGroup';
	
	/**
	 * list of applications
	 * @var	array<wcf\data\application\Application>
	 */
	protected $applications = array();
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * Assigns an application to this group.
	 * 
	 * @param	wcf\data\application\Application	$application
	 */
	public function addApplication(Application $application) {
		if ($this->groupID == $application->groupID) {
			$this->applications[] = $application;
		}
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->applications);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		return $this->applications[$this->index];
	}
	
	/**
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->applications[$this->index];
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->applications[$this->index]);
	}
}

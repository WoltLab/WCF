<?php
namespace wcf\system\database\statement;

/**
 * Similar to the regular `PreparedStatement` class, but throws an exception when trying to read data
 * before executing the statement at least once.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Database\Statement
 */
class DebugPreparedStatement extends PreparedStatement {
	protected $debugDidExecuteOnce = false;
	
	/**
	 * @inheritDoc
	 */
	public function __call($name, $arguments) {
		if ($name === 'fetchAll' || $name === 'fetchColumn') {
			$this->debugThrowIfNotExecutedBefore();
		}
		
		return parent::__call($name, $arguments);
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $parameters = []) {
		$this->debugDidExecuteOnce = true;
		
		parent::execute($parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchArray($type = null) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchArray($type);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchSingleRow($type = null) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchSingleRow($type);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchSingleColumn($columnNumber = 0) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchSingleColumn($columnNumber);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchObject($className) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchObject($className);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchObjects($className, $keyProperty = null) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchObjects($className, $keyProperty);
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchMap($keyColumn, $valueColumn, $uniqueKey = true) {
		$this->debugThrowIfNotExecutedBefore();
		
		return parent::fetchMap($keyColumn, $valueColumn, $uniqueKey);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function debugThrowIfNotExecutedBefore() {
		if (!$this->debugDidExecuteOnce) {
			throw new \RuntimeException('Attempted to fetch data from a statement without executing it at least once.');
		}
	}
}

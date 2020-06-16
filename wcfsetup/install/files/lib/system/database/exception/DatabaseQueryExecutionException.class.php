<?php
namespace wcf\system\database\exception;
use wcf\system\exception\IExtraInformationException;
use wcf\util\StringUtil;

/**
 * Denotes an error that is related to a specific database query.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Exception
 * @since	3.0
 */
class DatabaseQueryExecutionException extends DatabaseQueryException implements IExtraInformationException {
	/**
	 * Parameters that were passed to execute().
	 * @var	array
	 */
	protected $parameters = [];
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * @inheritDoc
	 */
	public function __construct($message, $parameters, \PDOException $previous = null) {
		parent::__construct($message, $previous);
		
		$this->parameters = $parameters;
	}
	
	/**
	 * Returns the parameters that were passed to execute().
	 *
	 * @return	array
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getExtraInformation() {
		$i = 0;
		
		return array_map(function ($val) use (&$i) {
			switch (gettype($val)) {
				case 'NULL':
					$val = 'null';
				break;
				case 'string':
					$val = "'".addcslashes(StringUtil::encodeHTML($val), "\\'")."'";
				break;
				case 'boolean':
					$val = $val ? 'true' : 'false';
				break;
			}
			
			return ['Query Parameter '.(++$i), $val];
		}, $this->getParameters());
	}
}

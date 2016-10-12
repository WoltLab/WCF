<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\IDatabaseObjectProcessor;

/**
 * Every concrete condition implementation needs to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
interface ICondition extends IDatabaseObjectProcessor {
	/**
	 * Returns the data saved with the condition used to check if the condition
	 * is fulfilled. If null is returned, there is no condition to be created.
	 * 
	 * @return	array|null
	 */
	public function getData();
	
	/**
	 * Returns the output for setting up the condition.
	 * 
	 * @return	string
	 */
	public function getHTML();
	
	/**
	 * Reads the form parameters of the condition.
	 */
	public function readFormParameters();
	
	/**
	 * Resets the internally stored condition data.
	 */
	public function reset();
	
	/**
	 * Extracts all needed data from the given condition to pre-fill the output
	 * for editing the given condition.
	 * 
	 * @param	Condition	$condition
	 */
	public function setData(Condition $condition);
	
	/**
	 * Validates the read condition data.
	 */
	public function validate();
}

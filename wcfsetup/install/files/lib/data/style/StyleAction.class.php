<?php
namespace wcf\data\style;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;

/**
 * Executes style-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category 	Community Framework
 */
class StyleAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\style\StyleEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$style = parent::create();
		
		// add variables
		if (isset($this->parameters['variables']) && !empty($this->parameters['variables'])) {
			$sql = "SELECT	variableID, variableName, defaultValue
				FROM	wcf".WCF_N."_style_variable";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$variables = array();
			while ($row = $statement->fetchArray()) {
				$variableName = $row['variableName'];
				
				// ignore variables with identical value
				if (isset($this->parameters['variables'][$variableName])) {
					if ($this->parameters['variables'][$variableName] == $row['defaultValue']) {
						continue;
					}
					else {
						$variables[$row['variableID']] = $this->parameters['variables'][$variableName];
					}
				}
			}
			
			// insert variables that differ from default values
			if (!empty($variables)) {
				$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
							(styleID, variableID, variableValue)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($variables as $variableID => $variableValue) {
					$statement->execute(array(
						$style->styleID,
						$variableID,
						$variableValue
					));
				}
				WCF::getDB()->commitTransaction();
			}
		}
		
		return $style;
	}
}

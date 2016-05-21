<?php
namespace wcf\data\style;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category	Community Framework
 *
 * @property-read	integer		$styleID
 * @property-read	integer		$packageID
 * @property-read	string		$styleName
 * @property-read	integer		$templateGroupID
 * @property-read	integer		$isDefault
 * @property-read	integer		$isDisabled
 * @property-read	string		$styleDescription
 * @property-read	string		$styleVersion
 * @property-read	string		$styleDate
 * @property-read	string		$image
 * @property-read	string		$copyright
 * @property-read	string		$license
 * @property-read	string		$authorName
 * @property-read	string		$authorURL
 * @property-read	string		$imagePath
 * @property-read	string		$packageName
 * @property-read	integer		$isTainted
 */
class Style extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'style';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'styleID';
	
	/**
	 * list of style variables
	 * @var	string[]
	 */
	protected $variables = [];
	
	const PREVIEW_IMAGE_MAX_HEIGHT = 64;
	const PREVIEW_IMAGE_MAX_WIDTH = 102;
	
	/**
	 * Returns the name of this style.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->styleName;
	}
	
	/**
	 * Returns the styles variables of this style.
	 * 
	 * @return	string[]
	 */
	public function getVariables() {
		$this->loadVariables();
		
		return $this->variables;
	}
	
	/**
	 * Returns a specific style variable or null if not found.
	 * 
	 * @param	string		$variableName
	 * @return	string
	 */
	public function getVariable($variableName) {
		if (isset($this->variables[$variableName])) {
			// check if variable is empty
			if ($this->variables[$variableName] == '~""') {
				return '';
			}
			
			return $this->variables[$variableName];
		}
		
		return null;
	}
	
	/**
	 * Loads style-specific variables.
	 */
	public function loadVariables() {
		if (!empty($this->variables)) {
			return;
		}
		
		$sql = "SELECT		variable.variableName, variable.defaultValue, value.variableValue
			FROM		wcf".WCF_N."_style_variable variable
			LEFT JOIN	wcf".WCF_N."_style_variable_value value
			ON		(value.variableID = variable.variableID AND value.styleID = ?)
			ORDER BY	variable.variableID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->styleID]);
		while ($row = $statement->fetchArray()) {
			$variableName = $row['variableName'];
			$variableValue = (isset($row['variableValue'])) ? $row['variableValue'] : $row['defaultValue'];
			
			$this->variables[$variableName] = $variableValue;
		}
	}
	
	/**
	 * Returns the style preview image path.
	 * 
	 * @return	string
	 */
	public function getPreviewImage() {
		if ($this->image && file_exists(WCF_DIR.'images/'.$this->image)) {
			return WCF::getPath().'images/'.$this->image;
		}
		
		return WCF::getPath().'images/stylePreview.png';
	}
	
	/**
	 * TODO: add documentation
	 * @since	2.2
	 */
	public static function splitLessVariables($variables) {
		$tmp = explode("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", $variables, 2);
		
		return [
			'preset' => $tmp[0],
			'custom' => (isset($tmp[1])) ? $tmp[1] : ''
		];
	}
	
	/**
	 * TODO: add documentation
	 * @since	2.2
	 */
	public static function joinLessVariables($preset, $custom) {
		if (empty($custom)) {
			return $preset;
		}
		
		return $preset . "/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n" . $custom;
	}
}

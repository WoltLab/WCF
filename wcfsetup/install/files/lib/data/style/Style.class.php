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
 * @package	WoltLabSuite\Core\Data\Style
 *
 * @property-read	integer		$styleID		unique id of the style
 * @property-read	integer		$packageID		id of the package which delivers the style
 * @property-read	string		$styleName		name of style
 * @property-read	integer		$templateGroupID	id of the template group used for the style or `0` if the style uses no specific template group 
 * @property-read	integer		$isDefault		is `1` if the style is the default style for guests and users, otherwise `0`
 * @property-read	integer		$isDisabled		is `1` if the style is disabled and thus cannot be used without having the specific permission to do so, otherwise `0`
 * @property-read	string		$styleDescription	description of the style or name of the language item which contains the description
 * @property-read	string		$styleVersion		version number of the style
 * @property-read	string		$styleDate		date when the used version of the style has been published
 * @property-read	string		$image			link or path (relative to `WCF_DIR`) to the preview image of the style
 * @property-read	string		$copyright		copyright text of the style
 * @property-read	string		$license		name of the style's license 
 * @property-read	string		$authorName		name(s) of the style's author(s)
 * @property-read	string		$authorURL		link to the author's website
 * @property-read	string		$imagePath		path (relative to `WCF_DIR`) to the images used by the style or empty if style has no special image path
 * @property-read	string		$packageName		package identifier used to export the style as a package or empty (thus style cannot be exported as package)
 * @property-read	integer		$isTainted		is `0` if the original declarations of an imported or installed style are not and cannot be altered, otherwise `1`
 */
class Style extends DatabaseObject {
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
	 * If $toHex is set to true the color defined by the variable
	 * will be converted to the hexadecimal notation (e.g. for use
	 * in emails)
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$toHex
	 * @return	string
	 */
	public function getVariable($variableName, $toHex = false) {
		if (isset($this->variables[$variableName])) {
			// check if variable is empty
			if ($this->variables[$variableName] == '~""') {
				return '';
			}
			
			if ($toHex && preg_match('/^rgba\((\d+), (\d+), (\d+), 1\)$/', $this->variables[$variableName], $matches)) {
				return sprintf('#%02x%02x%02x', $matches[1], $matches[2], $matches[3]);
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
			$variableValue = isset($row['variableValue']) ? $row['variableValue'] : $row['defaultValue'];
			
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
	 * 
	 * @param	string		$variables
	 * @return	array
	 * @since	3.0
	 */
	public static function splitLessVariables($variables) {
		$tmp = explode("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", $variables, 2);
		
		return [
			'preset' => $tmp[0],
			'custom' => isset($tmp[1]) ? $tmp[1] : ''
		];
	}
	
	/**
	 * TODO: add documentation
	 * 
	 * @param	string		$preset
	 * @param	string		$custom
	 * @return	string
	 * @since	3.0
	 */
	public static function joinLessVariables($preset, $custom) {
		if (empty($custom)) {
			return $preset;
		}
		
		return $preset . "/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n" . $custom;
	}
}

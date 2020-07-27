<?php
namespace wcf\data\style;
use wcf\data\DatabaseObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
 * @property-read	string		$image2x		link or path (relative to `WCF_DIR`) to the preview image of the style (2x version)
 * @property-read	string		$copyright		copyright text of the style
 * @property-read	string		$license		name of the style's license 
 * @property-read	string		$authorName		name(s) of the style's author(s)
 * @property-read	string		$authorURL		link to the author's website
 * @property-read	string		$imagePath		path (relative to `WCF_DIR`) to the images used by the style or empty if style has no special image path
 * @property-read	string		$packageName		package identifier used to export the style as a package or empty (thus style cannot be exported as package)
 * @property-read	integer		$isTainted		is `0` if the original declarations of an imported or installed style are not and cannot be altered, otherwise `1`
 * @property-read	integer		$hasFavicon		is `0` if the default favicon data should be used
 * @property-read	integer		$coverPhotoExtension	extension of the style's cover photo file
 * @property-read       string          $apiVersion             the style's compatibility version, possible values: '3.0' or '3.1'
 */
class Style extends DatabaseObject {
	/**
	 * list of style variables
	 * @var	string[]
	 */
	protected $variables = [];
	
	/**
	 * list of supported API versions
	 * @var string[]
	 */
	public static $supportedApiVersions = ['3.0', '3.1', '5.2'];
	
	const API_VERSION = '5.2';
	
	const PREVIEW_IMAGE_MAX_HEIGHT = 64;
	const PREVIEW_IMAGE_MAX_WIDTH = 102;
	
	const FAVICON_IMAGE_HEIGHT = 256;
	const FAVICON_IMAGE_WIDTH = 256;
	
	const BASE_ASSET_PATH = WCF_DIR.'images/';
	
	/**
	 * Returns the name of this style.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->styleName;
	}
	
	/**
	 * Returns the absolute path to the style's asset folder.
	 * 
	 * @return	string
	 * @since	5.3
	 */
	public function getAssetPath() {
		return FileUtil::addTrailingSlash(static::BASE_ASSET_PATH . 'style-' . $this->styleID);
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
			
			if ($toHex && preg_match('/^rgba\((\d+), (\d+), (\d+), (1|0?\.\d+)\)$/', $this->variables[$variableName], $matches)) {
				$r = $matches[1];
				$g = $matches[2];
				$b = $matches[3];
				$a = floatval($matches[4]);
				
				// calculate alpha value assuming a white canvas, source rgb will be (255,255,255) or #fff
				// see https://stackoverflow.com/a/2049362
				if ($a < 1) {
					$r = ((1 - $a) * 255) + ($a * $r);
					$g = ((1 - $a) * 255) + ($a * $g);
					$b = ((1 - $a) * 255) + ($a * $b);
					
					$clamp = function($v) {
						return max(0, min(255, intval($v)));
					};
					
					$r = $clamp($r);
					$g = $clamp($g);
					$b = $clamp($b);
				}
				
				return sprintf('#%02x%02x%02x', $r, $g, $b);
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
		
		// see https://github.com/WoltLab/WCF/issues/2636
		if (empty($this->variables['wcfPageThemeColor'])) {
			$this->variables['wcfPageThemeColor'] = $this->variables['wcfHeaderBackground'];
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
	 * Returns the style preview image path (2x version).
	 * 
	 * @return	string
	 */
	public function getPreviewImage2x() {
		if ($this->image2x && file_exists(WCF_DIR.'images/'.$this->image2x)) {
			return WCF::getPath().'images/'.$this->image2x;
		}
		
		return WCF::getPath().'images/stylePreview@2x.png';
	}
	
	/**
	 * Returns the absolute path to the apple touch icon.
	 * 
	 * @return	string
	 */
	public function getFaviconAppleTouchIcon() {
		return $this->getFaviconPath('apple-touch-icon.png');
	}
	
	/**
	 * Returns the absolute path to the `manifest.json` file.
	 * 
	 * @return	string
	 */
	public function getFaviconManifest() {
		return $this->getFaviconPath('manifest.json');
	}
	
	/**
	 * Returns the absolute path to the `browserconfig.xml` file.
	 *
	 * @return	string
	 */
	public function getFaviconBrowserconfig() {
		return $this->getFaviconPath('browserconfig.xml');
	}
	
	/**
	 * Returns the relative path to the favicon.
	 * 
	 * @return	string
	 */
	public function getRelativeFavicon() {
		return $this->getFaviconPath('favicon.ico', false);
	}
	
	/**
	 * Returns the cover photo filename.
	 * 
	 * @return      string
	 * @since 3.1
	 */
	public function getCoverPhoto() {
		if ($this->coverPhotoExtension) {
			return 'coverPhoto.'.$this->coverPhotoExtension;
		}
		
		return 'default.jpg';
	}
	
	/**
	 * @return string
	 * @since 5.2
	 */
	public function getCoverPhotoLocation() {
		if ($this->coverPhotoExtension) {
			return $this->getAssetPath().'coverPhoto.'.$this->coverPhotoExtension;
		}
		return WCF_DIR . 'images/coverPhotos/default.jpg';
	}
	
	/**
	 * @return string
	 * @since 5.2
	 */
	public function getCoverPhotoUrl() {
		if ($this->coverPhotoExtension) {
			return WCF::getPath() . FileUtil::getRelativePath(WCF_DIR, $this->getAssetPath()).'coverPhoto.'.$this->coverPhotoExtension;
		}
		return WCF::getPath() . 'images/coverPhotos/' . $this->getCoverPhoto();
	}
	
	/**
	 * Returns the path to a favicon-related file.
	 * 
	 * @param	string		$filename	name of the file
	 * @param	boolean		$absolutePath	if `true`, the absolute path is returned, otherwise the path relative to WCF is returned
	 * @return	string
	 */
	protected function getFaviconPath($filename, $absolutePath = true) {
		if ($filename === 'manifest.json') {
			if (ApplicationHandler::getInstance()->getActiveApplication()->domainName !== ApplicationHandler::getInstance()->getApplicationByID(1)->domainName) {
				return WCF::getPath() . 'images/favicon/corsProxy.php?type=manifest' . ($this->hasFavicon ? '&amp;styleID=' . $this->styleID : '');
			}
		}
		
		if ($this->hasFavicon) {
			$path = FileUtil::getRelativePath(WCF_DIR, $this->getAssetPath()).$filename;
		}
		else {
			$path = 'images/favicon/default.'.$filename;
		}
		
		if ($absolutePath) {
			return WCF::getPath() . $path;
		}
		
		return $path;
	}
	
	/**
	 * Splits the less variables string.
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
	 * Joins the less variables.
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

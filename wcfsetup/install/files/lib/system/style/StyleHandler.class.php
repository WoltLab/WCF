<?php
namespace wcf\system\style;
use wcf\data\style\ActiveStyle;
use wcf\data\style\Style;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.style
 * @category	Community Framework
 */
class StyleHandler extends SingletonFactory {
	/**
	 * style information cache
	 * @var	array
	 */
	protected $cache = array();
	
	/**
	 * active style object
	 * @var	\wcf\data\style\ActiveStyle
	 */
	protected $style = null;
	
	/**
	 * @see	\wcf\system\exception\SystemException::init()
	 */
	protected function init() {
		// load cache
		$this->cache = StyleCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Returns a list of all for the current user available styles.
	 * 
	 * @return	array<\wcf\data\style\Style>
	 */
	public function getAvailableStyles() {
		$styles = array();
		
		foreach ($this->cache['styles'] as $styleID => $style) {
			if (!$style->isDisabled || WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
				$styles[$styleID] = $style;
			}
		}
		
		return $styles;
	}
	
	/**
	 * Returns a list of all styles.
	 * 
	 * @return	array<\wcf\data\style\Style>
	 */
	public function getStyles() {
		return $this->cache['styles'];
	}
	
	/**
	 * Returns the active style.
	 * 
	 * @return	\wcf\data\style\ActiveStyle
	 */
	public function getStyle() {
		if ($this->style === null) {
			$this->changeStyle();
		}
		
		return $this->style;
	}
	
	/**
	 * Changes the active style.
	 * 
	 * @param	integer		$styleID
	 * @param	boolean		$ignorePermissions
	 */
	public function changeStyle($styleID = 0, $ignorePermissions = false) {
		// check permission
		if (!$ignorePermissions) {
			if (isset($this->cache['styles'][$styleID])) {
				if ($this->cache['styles'][$styleID]->isDisabled && !WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
					$styleID = 0;
				}
			}
		}
		
		// fallback to default style
		if (!isset($this->cache['styles'][$styleID])) {
			// get default style
			$styleID = $this->cache['default'];
			
			if (!isset($this->cache['styles'][$styleID])) {
				throw new SystemException('no default style defined');
			}
		}
		
		// init style
		$this->style = new ActiveStyle($this->cache['styles'][$styleID]);
		
		// set template group id
		if (WCF::getTPL()) {
			WCF::getTPL()->setTemplateGroupID($this->style->templateGroupID);
		}
	}
	
	/**
	 * Returns the HTML tag to include current stylesheet.
	 * 
	 * @return	string
	 */
	public function getStylesheet() {
		if (RequestHandler::getInstance()->isACPRequest()) {
			// ACP
			$filename = 'acp/style/style'.(WCF::getLanguage()->get('wcf.global.pageDirection') == 'rtl' ? '-rtl' : '').'.css';
			if (!file_exists(WCF_DIR.$filename)) {
				StyleCompiler::getInstance()->compileACP();
			}
		}
		else {
			// frontend
			$filename = 'style/style-'.$this->getStyle()->styleID.(WCF::getLanguage()->get('wcf.global.pageDirection') == 'rtl' ? '-rtl' : '').'.css';
			if (!file_exists(WCF_DIR.$filename)) {
				StyleCompiler::getInstance()->compile($this->getStyle()->getDecoratedObject());
			}
		}
		
		return '<link rel="stylesheet" type="text/css" href="'.WCF::getPath().$filename.'?m='.filemtime(WCF_DIR.$filename).'" />';
	}
	
	/**
	 * Resets stylesheet for given style.
	 * 
	 * @param	\wcf\data\style\Style	$style
	 */
	public function resetStylesheet(Style $style) {
		$stylesheets = glob(WCF_DIR.'style/style-'.$style->styleID.'*.css');
		if ($stylesheets !== false) {
			foreach ($stylesheets as $stylesheet) {
				@unlink($stylesheet);
			}
		}
	}
	
	/**
	 * Returns number of available styles.
	 * 
	 * @return	integer
	 */
	public function countStyles() {
		return count($this->getAvailableStyles());
	}
	
	/**
	 * Resets all stylesheets.
	 */
	public static function resetStylesheets() {
		// frontend stylesheets
		$stylesheets = glob(WCF_DIR.'style/style-*.css');
		if ($stylesheets !== false) {
			foreach ($stylesheets as $stylesheet) {
				@unlink($stylesheet);
			}
		}
		
		// ACP stylesheets
		$stylesheets = glob(WCF_DIR.'acp/style/style*.css');
		if ($stylesheets !== false) {
			foreach ($stylesheets as $stylesheet) {
				@unlink($stylesheet);
			}
		}
	}
}

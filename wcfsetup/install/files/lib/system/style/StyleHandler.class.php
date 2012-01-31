<?php
namespace wcf\system\style;
use wcf\data\style\ActiveStyle;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles style-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.style
 * @category 	Community Framework
 */
class StyleHandler extends SingletonFactory {
	/**
	 * style information cache
	 * @var	array
	 */
	protected $cache = array();
	
	/**
	 * active style object
	 * @var	wcf\data\style\ActiveStyle
	 */
	protected $style = null;
	
	/**
	 * Creates a new StyleHandler object.
	 */
	protected function init() {
		// load cache
		CacheHandler::getInstance()->addResource(
			'styles',
			WCF_DIR.'cache/cache.styles.php',
			'wcf\system\cache\builder\StyleCacheBuilder'
		);
		$this->cache = CacheHandler::getInstance()->get('styles');
	}
	
	/**
	 * Returns a list of all for the current user available styles.
	 * 
	 * @return	array<wcf\data\style\Style>
	 */
	public function getAvailableStyles() {
		$styles = array();
		
		foreach ($this->cache['styles'] as $styleID => $style) {
			if ((!$style->disabled && empty($this->cache['packages'][PACKAGE_ID]['disabled'][$styleID])) || WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
				$styles[$styleID] = $style;
			}
		}
		
		return $styles;
	}
	
	/**
	 * Returns the active style.
	 * 
	 * @return	wcf\data\style\ActiveStyle
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
				if (($this->cache['styles'][$styleID]->disabled || !empty($this->cache['packages'][PACKAGE_ID]['disabled'][$styleID])) && !WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
					$styleID = 0;
				}
			}
		}
		
		// fallback to default style
		if (!isset($this->cache['styles'][$styleID])) {
			// get package default style
			if (!empty($this->cache['packages'][PACKAGE_ID]['default'])) {
				$styleID = $this->cache['packages'][PACKAGE_ID]['default'];
			}
			// get global default style
			else {
				$styleID = $this->cache['default'];
			}
			
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
}

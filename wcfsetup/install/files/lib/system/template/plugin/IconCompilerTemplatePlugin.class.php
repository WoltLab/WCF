<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'icon' compiler function compiles dynamic icon paths.
 *
 * Usage:
 * {icon size='L'}{$foo}{/icon}
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class IconCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * icon size
	 * @var string
	 */
	protected $size = '';
	
	/**
	 * valid icon sizes
	 * @var array<string>
	 */
	protected static $validSizes = array('S', 'M', 'L');
	
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		// set default size
		$this->size = 'L';
		
		// get size
		if (isset($tagArgs['size'])) {
			if (strlen($tagArgs['size']) > 1) $tagArgs['size'] = substr($tagArgs['size'], 1, 1);
			if (in_array($tagArgs['size'], self::$validSizes)) $this->size = $tagArgs['size'];
		}

		$compiler->pushTag('icon');
		return "<?php ob_start(); ?>";
	}
	
	/**
	 * @see wcf\system\template\ICompilerTemplatePlugin::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('icon');
		return "<?php echo wcf\system\style\StyleHandler::getInstance()->getStyle()->getIconPath(ob_get_clean(), '".$this->size."'); ?>";
	}
}

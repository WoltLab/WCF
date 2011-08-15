<?php
namespace wcf\system\template\plugin;
use wcf\system\request\LinkHandler;
use wcf\system\template\IBlockTemplatePlugin;
use wcf\system\template\TemplateEngine;

/**
 * Shortcut for usage of LinkHandler::getInstance()->getLink() in template scripting.
 * 
 * Usage:
 * {link application='wcf'}index.php{/link}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class LinkBlockTemplatePlugin implements IBlockTemplatePlugin {
	/**
	 * internal loop counter
	 * @var integer
	 */
	protected $counter = 0;
	
	/**
	 * @see wcf\system\template\IBlockTemplatePlugin::execute()
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj) {
		$application = 'wcf';
		if (!empty($tagArgs['application'])) {
			$application = $tagArgs['application'];
		}
		
		return LinkHandler::getInstance()->getLink($blockContent, $application);
	}
	
	/**
	 * @see wcf\system\template\IBlockTemplatePlugin::init()
	 */
	public function init($tagArgs, TemplateEngine $tplObj) {
		$this->counter = 0;
	}
	
	/**
	 * @see wcf\system\template\IBlockTemplatePlugin::next()
	 */
	public function next(TemplateEngine $tplObj) {
		if ($this->counter == 0) {
			$this->counter++;
			return true;
		}
		
		return false;
	}
}

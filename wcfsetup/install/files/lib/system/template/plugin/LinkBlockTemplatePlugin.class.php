<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

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
		if (!isset($tagArgs['controller'])) throw new SystemException("missing 'controller' argument in pages tag");
		if (!isset($tagArgs['application']) || empty($tagArgs['application'])) {
			$tagArgs['application'] = 'wcf';
		}
		
		if (isset($tagArgs['encode']) && !$tagArgs['encode']) {
			return LinkHandler::getInstance()->getLink($tagArgs['controller'], $tagArgs, $blockContent);
		}
		
		return StringUtil::encodeHTML(LinkHandler::getInstance()->getLink($tagArgs['controller'], $tagArgs, $blockContent));
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

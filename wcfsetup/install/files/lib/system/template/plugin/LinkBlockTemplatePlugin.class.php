<?php
namespace wcf\system\template\plugin;
use wcf\system\request\LinkHandler;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template block plugin which generates a link using LinkHandler.
 * 
 * Usage:
 * 	{link application='wcf'}index.php{/link}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class LinkBlockTemplatePlugin implements IBlockTemplatePlugin {
	/**
	 * internal loop counter
	 * @var	integer
	 */
	protected $counter = 0;
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj) {
		if (!array_key_exists('controller', $tagArgs)) {
			$tagArgs['controller'] = null;
		}
		
		if (!isset($tagArgs['application']) || empty($tagArgs['application'])) {
			$tagArgs['application'] = 'wcf';
		}
		
		if (isset($tagArgs['isEmail']) && $tagArgs['isEmail']) {
			$tagArgs['encode'] = false;
		}
		
		if (isset($tagArgs['encode']) && !$tagArgs['encode']) {
			unset($tagArgs['encode']);
			return LinkHandler::getInstance()->getLink($tagArgs['controller'], $tagArgs, $blockContent);
		}
		
		return StringUtil::encodeHTML(LinkHandler::getInstance()->getLink($tagArgs['controller'], $tagArgs, $blockContent));
	}
	
	/**
	 * @inheritDoc
	 */
	public function init($tagArgs, TemplateEngine $tplObj) {
		$this->counter = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function next(TemplateEngine $tplObj) {
		if ($this->counter == 0) {
			$this->counter++;
			return true;
		}
		
		return false;
	}
}

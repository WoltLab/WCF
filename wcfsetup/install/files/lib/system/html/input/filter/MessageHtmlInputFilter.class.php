<?php
namespace wcf\system\html\input\filter;
use wcf\system\event\EventHandler;

/**
 * HTML input filter using HTMLPurifier.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input\Filter
 * @since       3.0
 */
class MessageHtmlInputFilter implements IHtmlInputFilter {
	/**
	 * @var	\HTMLPurifier
	 */
	protected static $purifier;
	
	/**
	 * Applies HTMLPurifier's filter on provided HTML.
	 * 
	 * @param       string  $html   unsafe HTML
	 * @return      string  sanitized HTML
	 */
	public function apply($html) {
		// work-around for a libxml bug that causes a single space between
		// some inline elements to be dropped 
		$html = str_replace('> <', '>&nbsp;<', $html);
	
		$html = $this->getPurifier()->purify($html);
		
		// work-around for a libxml bug that causes a single space between
		// some inline elements to be dropped
		$html = str_replace('&nbsp;', ' ', $html);
		
		return $html;
	}
	
	/**
	 * @return	\HTMLPurifier
	 */
	protected function getPurifier() {
		if (self::$purifier === null) {
			require_once(WCF_DIR . 'lib/system/html/input/filter/HTMLPurifier_URIScheme_steam.php');
			require_once(WCF_DIR . 'lib/system/html/input/filter/HTMLPurifier_URIScheme_ts3server.php');
			
			$config = \HTMLPurifier_Config::createDefault();
			
			// we need to prevent automatic finalization, otherwise we cannot read the default
			// value for `URI.AllowedSchemes` below
			$config->autoFinalize = false;
			
			$config->set('CSS.AllowedProperties', ['color', 'font-family', 'font-size']);
			$config->set('HTML.ForbiddenAttributes', ['*@lang', '*@xml:lang']);
			
			$allowedSchemes = $config->get('URI.AllowedSchemes');
			$allowedSchemes['steam'] = true;
			$allowedSchemes['ts3server'] = true;
			$config->set('URI.AllowedSchemes', $allowedSchemes);
			
			$this->setAttributeDefinitions($config);
			
			// enable IDN support, requires PEAR Net_IDNA2
			$config->set('Core.EnableIDNA', true);
			
			// enable finalization again, mimics the default behavior
			$config->autoFinalize = true;
			$config->finalize();
			
			self::$purifier = new \HTMLPurifier($config);
		}
		
		return self::$purifier;
	}
	
	/**
	 * Sets required configuration data for HTML filter.
	 * 
	 * @param       \HTMLPurifier_Config    $config         HTMLPurifier configuration
	 */
	protected function setAttributeDefinitions(\HTMLPurifier_Config $config) {
		$definition = $config->getHTMLDefinition(true);
		
		// code
		$definition->addAttribute('pre', 'data-file', 'Text');
		$definition->addAttribute('pre', 'data-line', 'Number');
		$definition->addAttribute('pre', 'data-highlighter', 'Text');
		
		// media
		$definition->addAttribute('img', 'data-media-id', 'Number');
		$definition->addAttribute('img', 'data-media-size', new \HTMLPurifier_AttrDef_Enum(['small', 'medium', 'large', 'original']));
		
		// quote
		$definition->addElement('woltlab-quote', 'Block', 'Flow', '', [
			'data-author' => 'Text',
			'data-link' => 'URI'
		]);
		
		// spoiler
		$definition->addElement('woltlab-spoiler', 'Block', 'Flow', '', [
			'data-label' => 'Text'
		]);
		
		// generic metacode
		$definition->addElement('woltlab-metacode', 'Inline', 'Inline', '', [
			'data-attributes' => 'Text',
			'data-name' => 'Text'
		]);
		
		// metacode markers
		$definition->addElement('woltlab-metacode-marker', 'Inline', 'Empty', '', [
			'data-attributes' => 'Text',
			'data-name' => 'Text',
			'data-source' => 'Text',
			'data-use-text' => 'Text',
			'data-uuid' => 'Text'
		]);
		
		// add data-attachment-id="" for <img>
		$definition->addAttribute('img', 'data-attachment-id', 'Number');
		$definition->addAttribute('img', 'srcset', 'Text');
		
		$parameters = [
			'config' => $config,
			'definition' => $definition
		];
		EventHandler::getInstance()->fireAction($this, 'setAttributeDefinitions', $parameters);
	}
}

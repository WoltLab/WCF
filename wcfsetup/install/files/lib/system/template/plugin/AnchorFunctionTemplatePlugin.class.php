<?php
namespace wcf\system\template\plugin;
use wcf\data\ILinkableObject;
use wcf\data\ITitledLinkObject;
use wcf\data\ITitledObject;
use wcf\system\template\TemplateEngine;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Template function plugin which generate `a` HTML elements.
 * 
 * Required parameters are either:
 * 	`object` (`ITitledLinkObject`)
 * or both:
 * 	`link` (`ILinkableObject`)
 * 	`content` (`ITitledObject`, object with `__toString()` method, or string)
 * 
 * The only additional parameter that is treated in a special way is `append` whose value is appended
 * to the link.
 * All other additional parameter values are added as attributes to the `a` element. Parameter names
 * in camel case are changed to kebab case (`fooBar` becomes `foo-bar`).
 * 
 * The only additional parameter name that is disallowed is `href`.
 *
 * Usage:
 * 	{anchor object=$object data-foo='bar'}
 * 	{anchor object=$object}
 * 	{anchor object=$object append='#anchor'}
 * 	{anchor link=$linkObject content=$titleObject}
 * 	{anchor link=$linkObject content='Title'}
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	5.3
 */
class AnchorFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$link = $content = null;
		if (isset($tagArgs['object'])) {
			$object = $tagArgs['object'];
			unset($tagArgs['object']);
			
			if (!($object instanceof ITitledLinkObject) && !ClassUtil::isDecoratedInstanceOf($object, ITitledLinkObject::class)) {
				throw new \InvalidArgumentException("'object' attribute does not implement interface '" . ITitledLinkObject::class . "'.");
			}
			
			$link = $object->getLink();
			$content = $object->getTitle();
		}
		else if (isset($tagArgs['link']) && isset($tagArgs['content'])) {
			if (!($tagArgs['link'] instanceof ILinkableObject) && !ClassUtil::isDecoratedInstanceOf($tagArgs['link'], ITitledLinkObject::class)) {
				throw new \InvalidArgumentException("'link' attribute does not implement interface '" . ILinkableObject::class . "'.");
			}
			
			$link = $tagArgs['link']->getLink();
			unset($tagArgs['link']);
			
			if (is_object($tagArgs['content'])) {
				if ($tagArgs['content'] instanceof ITitledObject || ClassUtil::isDecoratedInstanceOf($tagArgs['content'], ITitledObject::class)) {
					$content = $tagArgs['content']->getTitle();
				}
				else if (method_exists($tagArgs['content'], '__toString')) {
					$content = (string)$tagArgs['content'];
				}
				else {
					throw new \InvalidArgumentException("'content' object does not implement " . ITitledObject::class . ".");
				}
			}
			else if (is_string($tagArgs['content']) || is_numeric($tagArgs['content'])) {
				$content = $tagArgs['content'];
			}
			else {
				throw new \InvalidArgumentException("'content' attribute is of invalid type " . gettype($tagArgs['content']) . ".");
			}
			unset($tagArgs['content']);
		}
		else {
			throw new \InvalidArgumentException("Missing 'object' attribute or 'link' and 'content' attributes.");
		}
		
		if (isset($tagArgs['href'])) {
			throw new \InvalidArgumentException("'href' attribute is not allowed.");
		}
		
		$append = '';
		if (isset($tagArgs['append'])) {
			$append = $tagArgs['append'];
			unset($tagArgs['append']);
		}
		
		$additionalParameters = '';
		foreach ($tagArgs as $name => $value) {
			if (!preg_match('~[a-z]+([A-z]+)+~', $name)) {
				throw new \InvalidArgumentException("Invalid additional argument name '{$name}'.");
			}
			
			$additionalParameters .= ' ' . strtolower(preg_replace('~([A-Z])~', '-$1', $name)) . '="' . StringUtil::encodeHTML($value) . '"';
		}
		
		return '<a href="' . StringUtil::encodeHTML($link . $append) . '"' . $additionalParameters . '>' . StringUtil::encodeHTML($content) . '</a>';
	}
}

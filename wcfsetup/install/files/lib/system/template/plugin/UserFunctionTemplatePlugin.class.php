<?php
namespace wcf\system\template\plugin;
use wcf\data\user\UserProfile;
use wcf\system\template\TemplateEngine;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Template function plugin which generates links to user profiles.
 * 
 * Attributes:
 * - `object` (required) has to be a (decorated) `UserProfile` object.
 * - `type` (optional) supports the following values:
 *      - `default` (default value) generates a link with the formatted username with popover support.
 *      - `avatarXY` generates a link with the user's avatar in size `XY`.
 *      - `plain` generates a link link without username formatting and popover support
 * - `append` (optional) is appended to the user link.
 * 
 * All other additional parameter values are added as attributes to the `a` element. Parameter names
 * in camel case are changed to kebab case (`fooBar` becomes `foo-bar`).
 *
 * Usage:
 *      {user object=$user}
 *      {user object=$user type='plain'}
 *      {user object=$user type='avatar48'}
 *      {user object=$user append='#wall'}
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Template\Plugin
 * @since       5.3
 */
class UserFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (!isset($tagArgs['object'])) {
			throw new \InvalidArgumentException("Missing 'object' attribute.");
		}
		
		$object = $tagArgs['object'];
		unset($tagArgs['object']);
		if (!($object instanceof UserProfile) && !ClassUtil::isDecoratedInstanceOf($object, UserProfile::class)) {
			$type = gettype($object);
			if (is_object($object)) {
				$type = "'" . get_class($object) . "' object";
			}
			
			throw new \InvalidArgumentException("'object' attribute is no '" . UserProfile::class . "' object, instead {$type} given.");
		}
		
		$additionalParameters = '';
		$content = '';
		if (isset($tagArgs['type'])) {
			$type = $tagArgs['type'];
			unset($tagArgs['type']);
			
			if ($type === 'plain') {
				$content = StringUtil::encodeHTML($object->getTitle());
			}
			else if (preg_match('~^avatar(\d+)$~', $type, $matches)) {
				$content = $object->getAvatar()->getImageTag($matches[1]);
			}
			else if ($type !== 'default') {
				throw new \InvalidArgumentException("Unknown 'type' value '{$type}'.");
			}
		}
		
		// default case
		if ($content === '') {
			$additionalParameters = ' data-object-id="' . $object->getObjectID() . '"';
			$content = $object->getFormattedUsername();
			if (isset($tagArgs['class'])) {
				$tagArgs['class'] = 'userLink ' . $tagArgs['class'];
			}
			else {
				$tagArgs['class'] = 'userLink';
			}
		}
		
		if (isset($tagArgs['href'])) {
			throw new \InvalidArgumentException("'href' attribute is not allowed.");
		}
		
		$append = '';
		if (isset($tagArgs['append'])) {
			$append = $tagArgs['append'];
			unset($tagArgs['append']);
		}
		
		foreach ($tagArgs as $name => $value) {
			if (!preg_match('~^[a-z]+([A-z]+)+$~', $name)) {
				throw new \InvalidArgumentException("Invalid additional argument name '{$name}'.");
			}
			
			$additionalParameters .= ' ' . strtolower(preg_replace('~([A-Z])~', '-$1', $name))
				. '="' . StringUtil::encodeHTML($value) . '"';
		}
		
		return '<a href="' . StringUtil::encodeHTML($object->getLink() . $append) . '"' . $additionalParameters . '>' . $content . '</a>';
	}
}

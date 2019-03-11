<?php
namespace wcf\system\bbcode\media\provider;

/**
 * Interface for media provider callbacks.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode\Media\Provider
 * @since	3.1
 */
interface IBBCodeMediaProvider {
	/**
	 * Parses given media url and returns output html.
	 * 
	 * @param       string          $url            media url
	 * @param       string[]        $matches
	 * @return      string          output html
	 */
	public function parse($url, array $matches = []);
}

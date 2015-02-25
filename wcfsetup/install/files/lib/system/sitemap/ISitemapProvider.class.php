<?php
namespace wcf\system\sitemap;

/**
 * Provides a general interface for all sitemap entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.sitemap
 * @category	Community Framework
 */
interface ISitemapProvider {
	/**
	 * Returns the parsed sitemap template.
	 * 
	 * @return	string
	 */
	public function getTemplate();
}

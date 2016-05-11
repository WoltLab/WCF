<?php
namespace wcf\data\sitemap;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit sitemap entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
 *
 * @method	Sitemap		getDecoratedObject()
 * @mixin	Sitemap
 */
class SitemapEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Sitemap::class;
}

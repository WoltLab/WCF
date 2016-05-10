<?php
namespace wcf\data\sitemap;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of sitemap entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.sitemap
 * @category	Community Framework
 * 
 * @method	Sitemap		current()
 * @method	Sitemap[]	getObjects()
 * @method	Sitemap|null	search($objectID)
 * @property	Sitemap[]	$objects
 */
class SitemapList extends DatabaseObjectList { }

<?php
namespace wcf\system\box;

/**
 * Box for the tag cloud of articles.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 */
class ArticleTagCloudBoxController extends TagCloudBoxController {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.woltlab.wcf.article';
}

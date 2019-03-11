<?php
namespace wcf\system\importer;

/**
 * Imports article comment response.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class ArticleCommentResponseImporter extends AbstractCommentResponseImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.article.comment';
}

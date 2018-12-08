<?php
namespace wcf\acp\form;

/**
 * Shows the article category add form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class ArticleCategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.woltlab.wcf.article.category';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_ARTICLE'];
}

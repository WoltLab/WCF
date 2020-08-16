<?php
namespace wcf\acp\form;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the article category add form.
 *
 * @author      Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Form
 * @since       3.0
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
	
	/**
	 * @var string[]
	 * @since	5.2
	 */
	public $availableSortFields = [
		'publicationDate',
		'title'
	];
	
	/**
	 * @var string
	 * @since	5.2
	 */
	public $sortField = 'publicationDate';
	
	/**
	 * @var string
	 * @since	5.2
	 */
	public $sortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['sortField'])) $this->sortField = StringUtil::trim($_POST['sortField']);
		if (isset($_POST['sortOrder'])) $this->sortOrder = StringUtil::trim($_POST['sortOrder']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (!in_array($this->sortField, $this->availableSortFields)) {
			throw new UserInputException('sortField');
		}
		
		if ($this->sortOrder !== 'ASC' && $this->sortOrder !== 'DESC') {
			throw new UserInputException('sortOrder');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		$this->additionalData['sortField'] = $this->sortField;
		$this->additionalData['sortOrder'] = $this->sortOrder;
		
		parent::save();
		
		WCF::getTPL()->assign([
			'objectEditLink' => LinkHandler::getInstance()->getLink('ArticleCategoryEdit', ['id' => $this->objectAction->getReturnValues()['returnValues']->categoryID]),
		]);
		
		$this->sortField = 'publicationDate';
		$this->sortOrder = 'DESC';
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'availableSortFields' => $this->availableSortFields,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder
		]);
	}
}

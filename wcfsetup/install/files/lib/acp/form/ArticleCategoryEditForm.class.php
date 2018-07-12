<?php
declare(strict_types=1);
namespace wcf\acp\form;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the article category edit form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Form
 * @since       3.0
 */
class ArticleCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.category.list';
	
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
	 * @since 3.2
	 */
	public $availableSortFields = [
		'publicationDate',
		'title'
	];
	
	/**
	 * @var string
	 * @since 3.2
	 */
	public $sortField = 'publicationDate';
	
	/**
	 * @var string
	 * @since 3.2
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
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->sortField = ($this->category->sortField ?: 'publicationDate');
			/** @noinspection PhpUndefinedFieldInspection */
			$this->sortOrder = ($this->category->sortOrder ?: 'DESC');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		$this->additionalData['sortField'] = $this->sortField;
		$this->additionalData['sortOrder'] = $this->sortOrder;
		
		parent::save();
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

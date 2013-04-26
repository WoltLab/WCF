<?php
namespace wcf\form;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation for edit Forms.
 *
 * @author	Jeffrey Reichardt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
abstract class AbstractEditForm extends AbstractAddForm {
	/**
	 * @see	wcf\page\AbstractPage::$action
	 */
	public $action = 'edit';

	/**
	 * object id
	 * @var	integer
	 */
	public $objectID = 0;

	/**
	 * Holds object
	 * @var	wcf\data\DatabaseObject
	 */
	public $object = null;

	/**
	 * Holds name of base class
	 * @var string
	 */
	public $className = '';

	/**
	 * @see	wcf\page\AbstractPage::__run()
	 */
	public function __run() {
		// set class name
		if (empty($this->className)) {
			$className = $this->actionClassName;

			if (StringUtil::substring($className, -6) == 'Action') {
				$this->className = StringUtil::substring($className, 0, -6);
			}
		}

		parent::__run();
	}

	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));

		if (isset($_REQUEST['id'])) $this->objectID = intval($_REQUEST['id']);
		$this->object = new $this->className($this->objectID);
		if (!$this->object->$indexName) {
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();

		// update label
		$this->objectAction = new $this->actionClassName(array($this->objectID), 'update', array('data' => $this->values));
		$this->objectAction->executeAction();

		$this->saved();

		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}

	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();

		if (empty($_POST)) {
			foreach ($this->fields as $key => $value) {
				if ($this->object->$key) {
					$this->fields[$key] = $this->object->$key;
				}
			}
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'objectID' => $this->objectID,
			'object' => $this->object
		));
	}
}

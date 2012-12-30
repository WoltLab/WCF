<?php
namespace wcf\acp\form;
use wcf\data\style\StyleEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Shows the style import form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.style
 * @subpackage	acp.form
 * @category	Community Framework
 */
class StyleImportForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style.import';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canAddStyle');
	
	/**
	 * upload data
	 * @var	array<string>
	 */
	public $source = array();
	
	/**
	 * style editor object
	 * @var	wcf\data\style\StyleEditor
	 */
	public $style = null;
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_FILES['source'])) $this->source = $_FILES['source'];
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
	
		if (empty($this->source['name'])) {
			throw new UserInputException('source');
		}
		
		if (empty($this->source['tmp_name'])) {
			throw new UserInputException('source', 'uploadFailed');
		}
		
		// get filename
		$this->source['name'] = FileUtil::getTemporaryFilename('style_', preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', basename($this->source['name'])));
		
		if (!@move_uploaded_file($this->source['tmp_name'], $this->source['name'])) {
			throw new UserInputException('source', 'uploadFailed');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		try {
			$this->style = StyleEditor::import($this->source['name']);
		}
		catch (\Exception $e) {
			@unlink($this->source['name']);
		}
		
		@unlink($this->source['name']);
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
}

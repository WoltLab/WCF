<?php
namespace wcf\acp\form;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the style export form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.style
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class StyleExportForm extends AbstractForm {
	/**
	 * true, if style has custom icons
	 * @var	boolean
	 */
	public $canExportIcons = false;
	
	/**
	 * true, if style has custom images
	 * @var	boolean
	 */
	public $canExportImages = false;
	
	/**
	 * true, if style has custom templates
	 * @var	boolean
	 */
	public $canExportTemplates = false;
	
	/**
	 * true, if icons should be exported
	 * @var	boolean
	 */
	public $exportIcons = false;
	
	/**
	 * true, if images should be exported
	 * @var	boolean
	 */
	public $exportImages = false;
	
	/**
	 * true, if templates should be exported
	 * @var	boolean
	 */
	public $exportTemplates = false;
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canEditStyle');
	
	/**
	 * style object
	 * @var	wcf\data\style\Style
	 */
	public $style = null;
	
	/**
	 * style id
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->styleID = intval($_REQUEST['id']);
		$this->style = new Style($this->styleID);
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		
		if ($this->style->iconPath && $this->style->iconPath != 'icon/') $this->canExportIcons = true;
		if ($this->style->imagePath && $this->style->imagePath != 'images/') $this->canExportImages = true;
		if ($this->style->templateGroupID) $this->canExportTemplates = true;
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->canExportIcons && isset($_POST['exportIcons'])) $this->exportIcons = true;
		if ($this->canExportImages && isset($_POST['exportImages'])) $this->exportImages = true;
		if ($this->canExportTemplates && isset($_POST['exportTemplates'])) $this->exportTemplates = true;
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get style filename
		$filename = str_replace(' ', '-', preg_replace('/[^a-z0-9 _-]/', '', StringUtil::toLowerCase($this->style->styleName)));
		
		// send headers
		header('Content-Type: application/x-gzip; charset=utf-8');
		header('Content-Disposition: attachment; filename="'.$filename.'-style.tgz"');
		
		// export style
		$styleEditor = new StyleEditor($this->style);
		$styleEditor->export($this->exportTemplates, $this->exportImages, $this->exportIcons);
		
		// call saved event
		$this->saved();
		
		exit;
	}
	
	/**
	 * @see	wcf\form\IForm::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'canExportIcons' => $this->canExportIcons,
			'canExportImages' => $this->canExportImages,
			'canExportTemplates' => $this->canExportTemplates,
			'exportIcons' => $this->exportIcons,
			'exportImages' => $this->exportImages,
			'exportTemplates' => $this->exportTemplates,
			'style' => $this->style,
			'styleID' => $this->styleID
		));
	}
}

<?php
namespace wcf\acp\form;
use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the style export form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class StyleExportForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style.list';
	
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
	 * export style as installable package
	 * @var	boolean
	 */
	public $exportAsPackage = false;
	
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
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.style.canManageStyle'];
	
	/**
	 * style object
	 * @var	Style
	 */
	public $style = null;
	
	/**
	 * style id
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->styleID = intval($_REQUEST['id']);
		$this->style = new Style($this->styleID);
		if (!$this->style->styleID) {
			throw new IllegalLinkException();
		}
		
		if ($this->style->imagePath && $this->style->imagePath != 'images/') $this->canExportImages = true;
		if ($this->style->templateGroupID) $this->canExportTemplates = true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->canExportImages && isset($_POST['exportImages'])) $this->exportImages = true;
		if ($this->canExportTemplates && isset($_POST['exportTemplates'])) $this->exportTemplates = true;
		
		if ($this->style->packageName && isset($_POST['exportAsPackage'])) {
			$this->exportAsPackage = true;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// get style filename
		$filename = str_replace(' ', '-', preg_replace('/[^a-z0-9 _-]/', '', mb_strtolower($this->style->styleName)));
		
		// send headers
		header('Content-Type: application/x-gzip; charset=utf-8');
		
		if ($this->exportAsPackage) {
			header('Content-Disposition: attachment; filename="'.$this->style->packageName.'.tar.gz"');
		}
		else {
			header('Content-Disposition: attachment; filename="'.$filename.'-style.tgz"');
		}
		
		// export style
		$styleEditor = new StyleEditor($this->style);
		$styleEditor->export($this->exportTemplates, $this->exportImages, ($this->exportAsPackage ? $this->style->packageName : ''));
		
		// call saved event
		$this->saved();
		
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'canExportImages' => $this->canExportImages,
			'canExportTemplates' => $this->canExportTemplates,
			'exportAsPackage' => $this->exportAsPackage,
			'exportImages' => $this->exportImages,
			'exportTemplates' => $this->exportTemplates,
			'style' => $this->style,
			'styleID' => $this->styleID
		]);
	}
}

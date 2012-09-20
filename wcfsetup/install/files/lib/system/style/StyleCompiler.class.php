<?php
namespace wcf\system\style;
use wcf\data\style\Style;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StyleUtil;

/**
 * Provides access to the LESS PHP compiler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.style
 * @category 	Community Framework
 */
class StyleCompiler extends SingletonFactory {
	/**
	 * less compiler object
	 * @var	\lessc
	 */
	protected $compiler = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		require_once(WCF_DIR.'lib/system/style/lessc.inc.php');
		$this->compiler = new \lessc();
		$this->compiler->setImportDir(array(WCF_DIR));
	}
	
	/**
	 * Compiles LESS stylesheets.
	 * 
	 * @param	wcf\data\style\Style	$style
	 */
	public function compile(Style $style) {
		// read stylesheets by dependency order
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("file_log.filename REGEXP ?", array('style/([a-zA-Z0-9\_\-\.]+)\.less'));
		$conditions->add("package_dependency.packageID = ?", array(ApplicationHandler::getInstance()->getPrimaryApplication()->packageID));
		
		$sql = "SELECT		file_log.filename, package.packageDir
			FROM		wcf".WCF_N."_package_installation_file_log file_log
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(file_log.packageID = package_dependency.dependency)
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(file_log.packageID = package.packageID)
			".$conditions."
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$files = array();
		while ($row = $statement->fetchArray()) {
			$files[] = WCF_DIR.$row['packageDir'].$row['filename'];
		}
		
		// load style variables
		$sql = "SELECT	variableName, variableValue
			FROM	wcf".WCF_N."_style_variable
			WHERE	styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$variables = array();
		$individualCss = $individualLess = '';
		while ($row = $statement->fetchArray()) {
			if ($row['variableName'] == 'individualCss') {
				$individualCss = $row['variableValue'];
			}
			else if ($row['variableName'] == 'individualLess') {
				$individualLess = $row['variableValue'];
			}
			else {
				$variables[$row['variableName']] = $row['variableValue'];
			}
		}
		
		$less = $this->bootstrap($variables, $individualLess);
		foreach ($files as $file) {
			$less .= $this->prepareFile($file);
		}
		
		// append individual CSS/LESS
		if ($individualCss) {
			$less .= $individualCss;
		}
		
		try {
			$content = $this->compiler->compile($less);
		}
		catch (\Exception $e) {
			throw new SystemException("Could not compile LESS: ".$e->getMessage(), 0, '', $e);
		}
		
		// write stylesheet
		file_put_contents(WCF_DIR.'style/style-'.ApplicationHandler::getInstance()->getPrimaryApplication()->packageID.'-'.$style->styleID.'.css', $content);
		
		// convert stylesheet to RTL
		$content = StyleUtil::convertCSSToRTL($content);
		
		// write stylesheet for RTL
		file_put_contents(WCF_DIR.'style/style-'.ApplicationHandler::getInstance()->getPrimaryApplication()->packageID.'-'.$style->styleID.'-rtl.css', $content);
	}
	
	/**
	 * Prepares the style compiler, adding variables to environment and appending
	 * individual LESS declarations to override variables.less's values.
	 * 
	 * @param	array<string>		$variables
	 * @param	string			$individualLess
	 * @return	string
	 */
	protected function bootstrap(array &$variables, $individualLess = '') {
		// add reset like a boss
		$content = $this->prepareFile(WCF_DIR.'style/bootstrap/reset.less');
		
		// override LESS variables
		$variablesContent = $this->prepareFile(WCF_DIR.'style/bootstrap/variables.less');
		if ($individualLess) {
			list($keywords, $values) = explode('=', explode("\n", $individualLess));
			if (count($keywords) != count($values)) {
				throw new SystemException("Could not override LESS variables, invalid input");
			}
			
			foreach ($keywords as $i => $keyword) {
				$variablesContent = preg_replace(
					'~^@'.$keyword.':.*$~imU',
					'@'.$keyword.': '.$values[$i].';',
					$variablesContent
				);
			}
		}
		$content .= $variablesContent;
		
		
		// apply style variables
		$this->compiler->setVariables($variables);
		
		// add mixins
		$content .= $this->prepareFile(WCF_DIR.'style/bootstrap/mixins.less');
		
		return $content;
	}
	
	/**
	 * Prepares a LESS stylesheet for importing.
	 * 
	 * @param	string		$filename
	 * @return	string
	 */
	protected function prepareFile($filename) {
		if (!file_exists($filename) || !is_readable($filename)) {
			throw new SystemException("Unable to access '".$filename."', does not exist or is not readable");
		}
		
		// use a relative path
		$filename = FileUtil::getRelativePath(WCF_DIR, dirname($filename)) . basename($filename);
		return '@import "'.$filename.'";'."\n";
	}
}

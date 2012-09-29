<?php
namespace wcf\system\style;
use wcf\data\style\Style;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\Callback;
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
		
		// get style variables
		$variables = $style->getVariables();
		$individualCss = '';
		if (isset($variables['individualCss'])) {
			$individualCss = $variables['individualCss'];
			unset($variables['individualCss']);
		}
		
		$this->compileStylesheet(
			WCF_DIR.'style/style-'.ApplicationHandler::getInstance()->getPrimaryApplication()->packageID.'-'.$style->styleID,
			$files,
			$variables,
			$individualCss,
			new Callback(function($content) use ($style) {
				return "/* stylesheet for '".$style->styleName."', generated on ".gmdate('r')." -- DO NOT EDIT */\n\n" . $content;
			})
		);
	}
	
	/**
	 * Compiles LESS stylesheets for ACP usage.
	 */
	public function compileACP() {
		$files = glob(WCF_DIR.'style/*.less');
		
		// read default values
		$sql = "SELECT		variableName, defaultValue
			FROM		wcf".WCF_N."_style_variable
			ORDER BY	variableID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = array();
		while ($row = $statement->fetchArray()) {
			$variables[$row['variableName']] = $row['defaultValue'];
		}
		
		$this->compileStylesheet(
			WCF_DIR.'acp/style/style',
			$files,
			$variables,
			'',
			new Callback(function($content) {
				// fix relative paths
				$content = str_replace('../icon/', '../../icon/', $content);
				$content = str_replace('../images/', '../../images/', $content);
				
				return "/* stylesheet for ACP, generated on ".gmdate('r')." -- DO NOT EDIT */\n\n" . $content;
			})
		);
	}
	
	/**
	 * Prepares the style compiler by adding variables to environment.
	 * 
	 * @param	array<string>		$variables
	 * @return	string
	 */
	protected function bootstrap(array $variables) {
		// add reset like a boss
		$content = $this->prepareFile(WCF_DIR.'style/bootstrap/reset.less');
		
		// apply style variables
		$this->compiler->setVariables($variables);
		
		// add mixins
		$content .= $this->prepareFile(WCF_DIR.'style/bootstrap/mixin.less');
		
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
	
	/**
	 * Compiles LESS stylesheets into one CSS-stylesheet and writes them
	 * to filesystem. Please be aware not to append '.css' within $filename!
	 * 
	 * @param	string			$filename
	 * @param	array<string>		$files
	 * @param	array<string>		$variables
	 * @param	string			$individualCss
	 * @param	wcf\system\Callback	$callback
	 */
	protected function compileStylesheet($filename, array $files, array $variables, $individualCss, Callback $callback) {
		// build LESS bootstrap
		$less = $this->bootstrap($variables);
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
		
		$content = $callback($content);
		
		// write stylesheet
		file_put_contents($filename.'.css', $content);
		
		// convert stylesheet to RTL
		$content = StyleUtil::convertCSSToRTL($content);
		
		// write stylesheet for RTL
		file_put_contents($filename.'-rtl.css', $content);
	}
}

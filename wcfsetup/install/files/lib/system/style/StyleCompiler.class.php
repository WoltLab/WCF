<?php
namespace wcf\system\style;
use wcf\data\style\Style;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\Callback;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\StyleUtil;

/**
 * Provides access to the LESS PHP compiler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.style
 * @category	Community Framework
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
		
		$sql = "SELECT		file_log.filename, package.packageDir
			FROM		wcf".WCF_N."_package_installation_file_log file_log
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(file_log.packageID = package.packageID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$files = array();
		while ($row = $statement->fetchArray()) {
			$files[] = WCF_DIR.$row['packageDir'].$row['filename'];
		}
		
		// get style variables
		$variables = $style->getVariables();
		$individualLess = '';
		if (isset($variables['individualLess'])) {
			$individualLess = $variables['individualLess'];
			unset($variables['individualLess']);
		}
		
		// apply overrides
		if (isset($variables['overrideLess'])) {
			$lines = explode("\n", StringUtil::unifyNewlines($variables['overrideLess']));
			foreach ($lines as $line) {
				if (preg_match('~^@([a-zA-Z]+): ?([@a-zA-Z0-9 ,\.\(\)\%\#-]+);$~', $line, $matches)) {
					$variables[$matches[1]] = $matches[2];
				}
			}
			unset($variables['overrideLess']);
		}
		
		$this->compileStylesheet(
			WCF_DIR.'style/style-'.$style->styleID,
			$files,
			$variables,
			$individualLess,
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
			$value = $row['defaultValue'];
			if (empty($value)) {
				$value = '~""';
			}
			
			$variables[$row['variableName']] = $value;
		}
		
		// insert blue temptation files
		array_unshift($files, WCF_DIR.'acp/style/blueTemptation/variables.less', WCF_DIR.'acp/style/blueTemptation/override.less');
		
		$this->compileStylesheet(
			WCF_DIR.'acp/style/style',
			$files,
			$variables,
			file_get_contents(WCF_DIR.'acp/style/blueTemptation/individual.less'),
			new Callback(function($content) {
				// fix relative paths
				$content = str_replace('../font/', '../../font/', $content);
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
	 * @param	string			$individualLess
	 * @param	wcf\system\Callback	$callback
	 */
	protected function compileStylesheet($filename, array $files, array $variables, $individualLess, Callback $callback) {
		// build LESS bootstrap
		$less = $this->bootstrap($variables);
		foreach ($files as $file) {
			$less .= $this->prepareFile($file);
		}
		
		// append individual CSS/LESS
		if ($individualLess) {
			$less .= $individualLess;
		}
		
		try {
			$content = $this->compiler->compile($less);
		}
		catch (\Exception $e) {
			throw new SystemException("Could not compile LESS: ".$e->getMessage(), 0, '', $e);
		}
		
		$content = $callback($content);
		
		// compress stylesheet
		$lines = explode("\n", $content);
		$content = $lines[0] . "\n" . $lines[1] . "\n";
		for ($i = 2, $length = count($lines); $i < $length; $i++) {
			$line = trim($lines[$i]);
			$content .= $line;
			
			switch (substr($line, -1)) {
				case ',':
					$content .= ' ';
				break;
				
				case '}':
					$content .= "\n";
				break;
			}
			
			if (substr($line, 0, 6) == '@media') {
				$content .= "\n";
			}
		}
		
		// write stylesheet
		file_put_contents($filename.'.css', $content);
		FileUtil::makeWritable($filename.'.css');
		
		// convert stylesheet to RTL
		$content = StyleUtil::convertCSSToRTL($content);
		
		// write stylesheet for RTL
		file_put_contents($filename.'-rtl.css', $content);
		FileUtil::makeWritable($filename.'-rtl.css');
	}
}

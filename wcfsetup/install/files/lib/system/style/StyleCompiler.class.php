<?php
namespace wcf\system\style;
use wcf\data\application\Application;
use wcf\data\option\Option;
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
 * Provides access to the SCSS PHP compiler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.style
 * @category	Community Framework
 */
class StyleCompiler extends SingletonFactory {
	/**
	 * SCSS compiler object
	 * @var	\Leafo\ScssPhp\Compiler
	 */
	protected $compiler = null;
	
	/**
	 * names of option types which are supported as additional variables
	 * @var	array<string>
	 */
	public static $supportedOptionType = ['boolean', 'integer'];
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		require_once(WCF_DIR.'lib/system/style/scssphp/scss.inc.php');
		$this->compiler = new \Leafo\ScssPhp\Compiler();
		$this->compiler->setImportPaths([WCF_DIR]);
	}
	
	/**
	 * Compiles SCSS stylesheets.
	 * 
	 * @param	\wcf\data\style\Style	$style
	 */
	public function compile(Style $style) {
		// read stylesheets by dependency order
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("filename REGEXP ?", ['style/([a-zA-Z0-9\_\-\.]+)\.scss']);
		
		// TESTING ONLY
		$conditions->add("packageID <> ?", [1]);
		// TESTING ONLY
		
		$sql = "SELECT		filename, application
			FROM		wcf".WCF_N."_package_installation_file_log
			".$conditions."
			ORDER BY	packageID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$files = [];
		
		// TESTING ONLY
		if ($handle = opendir(WCF_DIR.'style/')) {
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..' || $file === 'bootstrap' || is_file(WCF_DIR.'style/'.$file)) {
					continue;
				}
				
				$file = WCF_DIR."style/{$file}/";
				if ($innerHandle = opendir($file)) {
					while (($innerFile = readdir($innerHandle)) !== false) {
						if ($innerFile === '.' || $innerFile === '..' || !is_file($file.$innerFile) || !preg_match('~^[a-zA-Z]+\.scss~$', $innerFile)) {
							continue;
						}
						
						$files[] = $file.$innerFile;
					}
					closedir($innerHandle);
				}
			}
			
			closedir($handle);
		}
		// TESTING ONLY
		
		while ($row = $statement->fetchArray()) {
			$files[] = Application::getDirectory($row['application']).$row['filename'];
		}
		
		// get style variables
		$variables = $style->getVariables();
		$individualScss = '';
		if (isset($variables['individualScss'])) {
			$individualScss = $variables['individualScss'];
			unset($variables['individualScss']);
		}
		
		// add style image path
		$imagePath = '../images/';
		if ($style->imagePath) {
			$imagePath = FileUtil::getRelativePath(WCF_DIR . 'style/', WCF_DIR . $style->imagePath);
			$imagePath = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($imagePath));
		}
		$variables['style_image_path'] = "'{$imagePath}'";
		
		// apply overrides
		if (isset($variables['overrideScss'])) {
			$lines = explode("\n", StringUtil::unifyNewlines($variables['overrideScss']));
			foreach ($lines as $line) {
				if (preg_match('~^@([a-zA-Z]+): ?([@a-zA-Z0-9 ,\.\(\)\%\#-]+);$~', $line, $matches)) {
					$variables[$matches[1]] = $matches[2];
				}
			}
			unset($variables['overrideScss']);
		}
		
		$this->compileStylesheet(
			WCF_DIR.'style/style-'.$style->styleID,
			$files,
			$variables,
			$individualScss,
			new Callback(function($content) use ($style) {
				return "/* stylesheet for '".$style->styleName."', generated on ".gmdate('r')." -- DO NOT EDIT */\n\n" . $content;
			})
		);
	}
	
	/**
	 * Compiles SCSS stylesheets for ACP usage.
	 */
	public function compileACP() {
		// read stylesheets by dependency order
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("filename REGEXP ?", ['style/([a-zA-Z0-9\_\-\.]+)\.scss']);
		
		// TESTING ONLY
		$conditions->add("packageID <> ?", [1]);
		// TESTING ONLY
		
		$sql = "SELECT		filename, application
			FROM		wcf".WCF_N."_package_installation_file_log
			".$conditions."
			ORDER BY	packageID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$files = [];
		
		// TESTING ONLY
		if ($handle = opendir(WCF_DIR.'style/')) {
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..' || $file === 'bootstrap' || is_file(WCF_DIR.'style/'.$file)) {
					continue;
				}
				
				$file = WCF_DIR."style/{$file}/";
				if ($innerHandle = opendir($file)) {
					while (($innerFile = readdir($innerHandle)) !== false) {
						if ($innerFile === '.' || $innerFile === '..' || !is_file($file.$innerFile) || !preg_match('~^[a-zA-Z]+\.scss~$', $innerFile)) {
							continue;
						}
						
						$files[] = $file.$innerFile;
					}
					closedir($innerHandle);
				}
			}
			
			closedir($handle);
		}
		// TESTING ONLY
		
		//$files = glob(WCF_DIR.'style/*.less');
		
		// read default values
		$sql = "SELECT		variableName, defaultValue
			FROM		wcf".WCF_N."_style_variable
			ORDER BY	variableID ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = [];
		while ($row = $statement->fetchArray()) {
			$value = $row['defaultValue'];
			if (empty($value)) {
				$value = '~""';
			}
			
			$variables[$row['variableName']] = $value;
		}
		
		$variables['wcfFontFamily'] = $variables['wcfFontFamilyFallback'];
		if (!empty($variables['wcfFontFamilyGoogle'])) {
			$variables['wcfFontFamily'] = '"' . $variables['wcfFontFamilyGoogle'] . '", ' . $variables['wcfFontFamily'];
		}
		
		// insert blue temptation files
		//array_unshift($files, WCF_DIR.'acp/style/blueTemptation/variables.scss', WCF_DIR.'acp/style/blueTemptation/override.scss');
		
		$variables['style_image_path'] = "'../images/blueTemptation/'";
		
		$this->compileStylesheet(
			WCF_DIR.'acp/style/style',
			$files,
			$variables,
			'',//file_get_contents(WCF_DIR.'acp/style/blueTemptation/individual.scss'),
			new Callback(function($content) {
				// fix relative paths
				$content = str_replace('../font/', '../../font/', $content);
				$content = str_replace('../icon/', '../../icon/', $content);
				$content = preg_replace('~\.\./images/(?!blueTemptation)~', '../../images/', $content);
				
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
		$content = $this->prepareFile(WCF_DIR.'style/bootstrap/reset.scss');
		
		// apply style variables
		$this->compiler->setVariables($variables);
		
		// add mixins
		$content .= $this->prepareFile(WCF_DIR.'style/bootstrap/mixin.scss');
		
		return $content;
	}
	
	/**
	 * Prepares a SCSS stylesheet for importing.
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
	 * Compiles SCSS stylesheets into one CSS-stylesheet and writes them
	 * to filesystem. Please be aware not to append '.css' within $filename!
	 * 
	 * @param	string			$filename
	 * @param	array<string>		$files
	 * @param	array<string>		$variables
	 * @param	string			$individualScss
	 * @param	\wcf\system\Callback	$callback
	 */
	protected function compileStylesheet($filename, array $files, array $variables, $individualScss, Callback $callback) {
		foreach ($variables as &$value) {
			if (StringUtil::startsWith($value, '../')) {
				$value = '~"'.$value.'"';
			}
		}
		unset($value);
		
		$variables['wcfFontFamily'] = $variables['wcfFontFamilyFallback'];
		if (!empty($variables['wcfFontFamilyGoogle'])) {
			$variables['wcfFontFamily'] = '"' . $variables['wcfFontFamilyGoogle'] . '", ' . $variables['wcfFontFamily'];
		}
		
		// add options as SCSS variables
		if (PACKAGE_ID) {
			foreach (Option::getOptions() as $constantName => $option) {
				if (in_array($option->optionType, static::$supportedOptionType)) {
					$variables['wcf_option_'.mb_strtolower($constantName)] = '~"'.$option->optionValue.'"';
				}
			}
		}
		else {
			// workaround during setup
			$variables['wcf_option_attachment_thumbnail_height'] = '~"210"';
			$variables['wcf_option_attachment_thumbnail_width'] = '~"280"';
			$variables['wcf_option_signature_max_image_height'] = '~"150"';
		}
		
		// build SCSS bootstrap
		$scss = $this->bootstrap($variables);
		foreach ($files as $file) {
			$scss .= $this->prepareFile($file);
		}
		
		// append individual CSS/SCSS
		if ($individualScss) {
			$scss .= $individualScss;
		}
		
		try {
			$this->compiler->setFormatter('Leafo\ScssPhp\Formatter\Crunched');
			$content = $this->compiler->compile($scss);
		}
		catch (\Exception $e) {
			throw new SystemException("Could not compile SCSS: ".$e->getMessage(), 0, '', $e);
		}
		
		$content = $callback($content);
		
		// compress stylesheet
		/*$lines = explode("\n", $content);
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
		*/
		
		// write stylesheet
		file_put_contents($filename.'.css', $content);
		FileUtil::makeWritable($filename.'.css');
		
		// convert stylesheet to RTL
		//$content = StyleUtil::convertCSSToRTL($content);
		
		// write stylesheet for RTL
		file_put_contents($filename.'-rtl.css', $content);
		FileUtil::makeWritable($filename.'-rtl.css');
	}
}

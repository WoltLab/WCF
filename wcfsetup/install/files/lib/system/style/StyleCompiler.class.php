<?php
namespace wcf\system\style;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Crunched as CrunchedFormatter;
use wcf\data\application\Application;
use wcf\data\option\Option;
use wcf\data\style\Style;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\StyleUtil;

/**
 * Provides access to the SCSS PHP compiler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Style
 */
class StyleCompiler extends SingletonFactory {
	/**
	 * SCSS compiler object
	 * @var	Compiler
	 */
	protected $compiler = null;
	
	/**
	 * names of option types which are supported as additional variables
	 * @var	string[]
	 */
	public static $supportedOptionType = ['boolean', 'float', 'integer', 'radioButton', 'select'];
	
	/**
	 * file used to store global SCSS declarations, relative to `WCF_DIR`
	 * @var string
	 */
	const FILE_GLOBAL_VALUES = 'style/ui/zzz_wsc_style_global_values.scss';
	
	/**
	 * registry keys for data storage
	 * @var string
	 */
	const REGISTRY_GLOBAL_VALUES = 'styleGlobalValues';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		require_once(WCF_DIR.'lib/system/style/scssphp/scss.inc.php');
		$this->compiler = new Compiler();
		// Disable Unicode support because of its horrible performance (7x slowdown)
		// https://github.com/WoltLab/WCF/pull/2736#issuecomment-416084079
		$this->compiler->setEncoding('iso8859-1');
		$this->compiler->setImportPaths([WCF_DIR]);
	}
	
	/**
	 * Compiles SCSS stylesheets.
	 * 
	 * @param	Style	$style
	 */
	public function compile(Style $style) {
		$files = $this->getCoreFiles();
		
		// read stylesheets in dependency order
		$sql = "SELECT		filename, application
			FROM		wcf".WCF_N."_package_installation_file_log
			WHERE           CONVERT(filename using utf8) REGEXP ?
					AND packageID <> ?
			ORDER BY	packageID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			'style/([a-zA-Z0-9\-\.]+)\.scss',
			1
		]);
		while ($row = $statement->fetchArray()) {
			// the global values will always be evaluated last
			if ($row['filename'] === self::FILE_GLOBAL_VALUES) {
				continue;
			}
			
			$files[] = Application::getDirectory($row['application']).$row['filename'];
		}
		
		// global SCSS
		if (file_exists(WCF_DIR . self::FILE_GLOBAL_VALUES)) {
			$files[] = WCF_DIR . self::FILE_GLOBAL_VALUES;
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
		
		// api version
		$variables['apiVersion'] = $style->apiVersion;
		
		$parameters = ['scss' => ''];
		EventHandler::getInstance()->fireAction($this, 'compile', $parameters);
		
		$this->compileStylesheet(
			WCF_DIR.'style/style-'.$style->styleID,
			$files,
			$variables,
			$individualScss . (!empty($parameters['scss']) ? "\n" . $parameters['scss'] : ''),
			function($content) use ($style) {
				return "/* stylesheet for '".$style->styleName."', generated on ".gmdate('r')." -- DO NOT EDIT */\n\n" . $content;
			}
		);
	}
	
	/**
	 * Compiles SCSS stylesheets for ACP usage.
	 */
	public function compileACP() {
		if (substr(WCF_VERSION, 0, 3) == '2.1') {
			// work-around for wcf2.1 update
			return;
		}
		
		$files = $this->getCoreFiles();
		
		// ACP uses a slightly different layout
		$files[] = WCF_DIR . 'acp/style/layout.scss';
		
		// include stylesheets from other apps in arbitrary order
		if (PACKAGE_ID) {
			foreach (ApplicationHandler::getInstance()->getApplications() as $application) {
				$files = array_merge($files, $this->getAcpStylesheets($application));
			}
		}
		
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
		
		$variables['style_image_path'] = "'../images/'";
		
		$this->compileStylesheet(
			WCF_DIR.'acp/style/style',
			$files,
			$variables,
			'',
			function($content) {
				// fix relative paths
				$content = str_replace('../font/', '../../font/', $content);
				$content = str_replace('../icon/', '../../icon/', $content);
				$content = preg_replace('~\.\./images/~', '../../images/', $content);
				
				return "/* stylesheet for ACP, generated on ".gmdate('r')." -- DO NOT EDIT */\n\n" . $content;
			}
		);
	}
	
	/**
	 * Returns a list of common stylesheets provided by the core.
	 * 
	 * @return      string[]        list of common stylesheets
	 */
	protected function getCoreFiles() {
		$files = [];
		if ($handle = opendir(WCF_DIR.'style/')) {
			while (($file = readdir($handle)) !== false) {
				if ($file === '.' || $file === '..' || $file === 'bootstrap' || is_file(WCF_DIR.'style/'.$file)) {
					continue;
				}
				
				$file = WCF_DIR."style/{$file}/";
				if ($innerHandle = opendir($file)) {
					while (($innerFile = readdir($innerHandle)) !== false) {
						if ($innerFile === '.' || $innerFile === '..' || !is_file($file.$innerFile) || !preg_match('~^[a-zA-Z0-9\-\.]+\.scss$~', $innerFile)) {
							continue;
						}
						
						$files[] = $file.$innerFile;
					}
					closedir($innerHandle);
				}
			}
			
			closedir($handle);
			
			// directory order is not deterministic in some cases
			sort($files);
		}
		
		return $files;
	}
	
	/**
	 * Returns the list of SCSS stylesheets of an application.
	 * 
	 * @param       Application     $application
	 * @return      string[]
	 */
	protected function getAcpStylesheets(Application $application) {
		if ($application->packageID == 1) return [];
		
		$files = [];
		
		$basePath = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $application->getPackage()->packageDir)) . 'acp/style/';
		$result = glob($basePath . '*.scss');
		if (is_array($result)) {
			foreach ($result as $file) {
				$files[] = $file;
			}
		}
		
		return $files;
	}
	
	/**
	 * Prepares the style compiler by adding variables to environment.
	 * 
	 * @param	string[]		$variables
	 * @return	string
	 */
	protected function bootstrap(array $variables) {
		// add reset like a boss
		$content = $this->prepareFile(WCF_DIR.'style/bootstrap/reset.scss');
		
		// apply style variables
		$this->compiler->setVariables($variables);
		
		// add mixins
		$content .= $this->prepareFile(WCF_DIR.'style/bootstrap/mixin.scss');
		
		// add newer mixins added with version 3.0
		foreach (glob(WCF_DIR.'style/bootstrap/mixin/*.scss') as $mixin) {
			$content .= $this->prepareFile($mixin);
		}
		
		if (ApplicationHandler::getInstance()->isMultiDomainSetup()) {
			$content .= <<<'EOT'
				@function getFont($filename, $family: "/", $version: "") {
					@return "../font/getFont.php?family=" + $family + "&filename=" + $filename + "&v=" + $version;
				}
EOT;
		}
		else {
			$content .= <<<'EOT'
				@function getFont($filename, $family: "/", $version: "") {
					@if ($family != "") {
						$family: "families/" + $family + "/";
					}
					@if ($version != "") {
						$version: "?v=" + $version;
					}
					
					@return "../font/" + $family + $filename + $version;
				}
EOT;
		}
		
		// add google fonts
		if (!empty($variables['wcfFontFamilyGoogle']) && PACKAGE_ID) {
			$cssFile = FontManager::getInstance()->getCssFilename(substr($variables['wcfFontFamilyGoogle'], 1, -1));
			if (is_readable($cssFile)) {
				$content .= file_get_contents($cssFile);
			}
		}
		
		return $content;
	}
	
	/**
	 * Prepares a SCSS stylesheet for importing.
	 * 
	 * @param	string		$filename
	 * @return	string
	 * @throws	SystemException
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
	 * @param	string		$filename
	 * @param	string[]	$files
	 * @param	string[]	$variables
	 * @param	string		$individualScss
	 * @param	callable	$callback
	 * @throws	SystemException
	 */
	protected function compileStylesheet($filename, array $files, array $variables, $individualScss, callable $callback) {
		foreach ($variables as &$value) {
			if (StringUtil::startsWith($value, '../')) {
				$value = '~"'.$value.'"';
			}
		}
		unset($value);
		
		$variables['wcfFontFamily'] = $variables['wcfFontFamilyFallback'];
		if (!empty($variables['wcfFontFamilyGoogle'])) {
			// The SCSS parser attempts to evaluate the variables, causing issues with font names that
			// include logical operators such as "And" or "Or".
			$variables['wcfFontFamilyGoogle'] = '"' . $variables['wcfFontFamilyGoogle'] . '"';
			
			$variables['wcfFontFamily'] = $variables['wcfFontFamilyGoogle'] . ', ' . $variables['wcfFontFamily'];
		}
		
		// add options as SCSS variables
		if (PACKAGE_ID) {
			foreach (Option::getOptions() as $constantName => $option) {
				if (in_array($option->optionType, static::$supportedOptionType)) {
					$variables['wcf_option_'.mb_strtolower($constantName)] = is_int($option->optionValue) ? $option->optionValue : '"'.$option->optionValue.'"';
				}
			}
			
			// api version
			if (!isset($variables['apiVersion'])) $variables['apiVersion'] = Style::API_VERSION;
		}
		else {
			// workaround during setup
			$variables['wcf_option_attachment_thumbnail_height'] = '~"210"';
			$variables['wcf_option_attachment_thumbnail_width'] = '~"280"';
			$variables['wcf_option_signature_max_image_height'] = '~"150"';
			
			$variables['apiVersion'] = Style::API_VERSION;
		}
		
		// convert into numeric value for comparison, e.g. `3.1` -> `31`
		$variables['apiVersion'] = str_replace('.', '', $variables['apiVersion']);
		
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
			$this->compiler->setFormatter(CrunchedFormatter::class);
			$content = $this->compiler->compile($scss);
		}
		catch (\Exception $e) {
			throw new SystemException("Could not compile SCSS: ".$e->getMessage(), 0, '', $e);
		}
		
		$content = $callback($content);
		
		// write stylesheet
		file_put_contents($filename.'.css', $content);
		FileUtil::makeWritable($filename.'.css');
		
		// convert stylesheet to RTL
		$content = StyleUtil::convertCSSToRTL($content);
		
		// force code boxes to be always LTR
		$content .= "\n/* RTL fix for code boxes */\n";
		$content .= '.codeBox > div > ol > li > span:last-child, .redactor-layer pre { direction: ltr; text-align: left; } .codeBox > div > ol > li > span:last-child { display: block; }';
		
		// write stylesheet for RTL
		file_put_contents($filename.'-rtl.css', $content);
		FileUtil::makeWritable($filename.'-rtl.css');
	}
}

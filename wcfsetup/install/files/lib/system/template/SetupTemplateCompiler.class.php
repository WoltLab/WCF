<?php
namespace wcf\system\template;

/**
 * Compiles template source into valid PHP code.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template
 */
class SetupTemplateCompiler extends TemplateCompiler {
	/**
	 * @inheritDoc
	 */
	protected function compileOutputTag($tag) {
		$encodeHTML = false;
		$formatNumeric = false;
		if ($tag[0] == '@') {
			$tag = mb_substr($tag, 1);
		}
		else if ($tag[0] == '#') {
			$tag = mb_substr($tag, 1);
			$formatNumeric = true;
		}
		else {
			$encodeHTML = true;
		}
		
		$parsedTag = $this->compileVariableTag($tag);
		
		// the @ operator at the beginning of an output avoids
		// the default call of StringUtil::encodeHTML()
		if ($encodeHTML) {
			$parsedTag = 'wcf\util\StringUtil::encodeHTML('.$parsedTag.')';
		}
		// the # operator at the beginning of an output instructs
		// the complier to call the StringUtil::formatNumeric() method
		else if ($formatNumeric) {
			$parsedTag = 'wcf\util\StringUtil::formatNumeric('.$parsedTag.')';
		}
		
		return '<?php echo '.$parsedTag.'; ?>';
	}
}

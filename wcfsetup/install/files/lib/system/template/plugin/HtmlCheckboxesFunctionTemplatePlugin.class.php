<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generates a list of html checkboxes.
 * 
 * Usage:
 * 	{htmlCheckboxes name="x" options=$array}
 * 	{htmlCheckboxes name="x" options=$array selected=$foo}
 * 	{htmlCheckboxes name="x" output=$outputArray}
 * 	{htmlCheckboxes name="x" output=$outputArray values=$valueArray}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class HtmlCheckboxesFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * indicates if encoding is disabled
	 * @var	boolean
	 */
	protected $disableEncoding = false;
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// get options
		if (isset($tagArgs['output']) && is_array($tagArgs['output'])) {
			if (isset($tagArgs['values']) && is_array($tagArgs['values'])) {
				$tagArgs['options'] = array_combine($tagArgs['values'], $tagArgs['output']);
			}
			else {
				$tagArgs['options'] = array_combine($tagArgs['output'], $tagArgs['output']);
			}
		}
		
		if (!isset($tagArgs['options']) || !is_array($tagArgs['options'])) {
			throw new SystemException("missing 'options' argument in htmlCheckboxes tag");
		}
		
		if (!isset($tagArgs['name'])) {
			throw new SystemException("missing 'name' argument in htmlCheckboxes tag");
		}
		
		if (isset($tagArgs['disableEncoding']) && $tagArgs['disableEncoding']) {
			$this->disableEncoding = true;
		}
		else {
			$this->disableEncoding = false;
		}
		
		// get selected values
		if (isset($tagArgs['selected'])) {
			if (!is_array($tagArgs['selected'])) $tagArgs['selected'] = [$tagArgs['selected']];
		}
		else {
			$tagArgs['selected'] = [];
		}
		if (!isset($tagArgs['separator'])) {
			$tagArgs['separator'] = '';
		}
		
		// build html
		$html = '';
		foreach ($tagArgs['options'] as $key => $value) {
			if (!empty($html)) $html .= $tagArgs['separator'];
			$html .= '<label><input type="checkbox" name="'.$this->encodeHTML($tagArgs['name']).'[]" value="'.$this->encodeHTML($key).'"'.(in_array($key, $tagArgs['selected']) ? ' checked' : '').'> '.$this->encodeHTML($value).'</label>';
		}
		
		return $html;
	}
	
	/**
	 * Executes StringUtil::encodeHTML on the given text if disableEncoding is false.
	 * @see	\wcf\util\StringUtil::encodeHTML()
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function encodeHTML($text) {
		if (!$this->disableEncoding) {
			$text = StringUtil::encodeHTML($text);
		}
		
		return $text;
	}
}

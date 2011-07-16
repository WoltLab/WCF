<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * The 'htmloptions' template function generates the options of an html select list.
 * 
 * Usage:
 * {htmloptions options=$array}
 * {htmloptions options=$array selected=$foo}
 * {htmloptions options=$array name="x"}
 * {htmloptions output=$outputArray}
 * {htmloptions output=$outputArray values=$valueArray}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginFunctionHtmloptions extends TemplatePluginFunctionHtmlcheckboxes {
	protected $selected = array();
	
	/**
	 * @see wcf\system\template\TemplatePluginFunction::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (isset($tagArgs['output']) && is_array($tagArgs['output'])) {
			if (count($tagArgs['output'])) {
				if (isset($tagArgs['values']) && is_array($tagArgs['values'])) {
					if (count($tagArgs['output']) == count($tagArgs['values'])) {
						$tagArgs['options'] = array_combine($tagArgs['values'], $tagArgs['output']);
					}
					else {
						$tagArgs['options'] = array();
					}
				}
				else {
					$tagArgs['options'] = array_combine($tagArgs['output'], $tagArgs['output']);
				}
			}
			else {
				$tagArgs['options'] = array();
			}
		}

		if (!isset($tagArgs['options']) || !is_array($tagArgs['options'])) {
			throw new SystemException("missing 'options' argument in htmloptions tag", 12001);
		}
		
		if (isset($tagArgs['disableEncoding']) && $tagArgs['disableEncoding']) {
			$this->disableEncoding = true;
		}
		else {
			$this->disableEncoding = false;
		}
		
		// get selected values
		$this->selected = array();
		if (isset($tagArgs['selected'])) {
			$this->selected = $tagArgs['selected'];
			if (!is_array($this->selected)) $this->selected = array($this->selected);	
		}
		
		// create option list
		$htmloptions = $this->makeOptionGroup(null, $tagArgs['options']);
		
		// create also a 'select' tag
		if (isset($tagArgs['name'])) {
			// unset all system vars
			unset($tagArgs['options'], $tagArgs['selected'], $tagArgs['output'], $tagArgs['values'], $tagArgs['disableEncoding']);
			
			// generate 'select' parameters
			$params = '';
			foreach ($tagArgs as $key => $value) {
				$params .= ' '.$key.'="'.$this->encodeHTML($value).'"';
			}
			
			$htmloptions = '<select'.$params.'>'."\n".$htmloptions."</select>\n";
		}
		
		return $htmloptions;
	}
	
	/**
	 * Makes the html for an option group.
	 * 
	 * @param	string		$key
	 * @param	array		$values
	 * @return	string				html code of an option group
	 */
	protected function makeOptionGroup($key, $values) {
		$html = '';
		if ($key !== null) {
			$html = '<optgroup label="'.$this->encodeHTML($key).'">'."\n";
		}
		
		foreach ($values as $childKey => $value) {
			if (is_array($value)) {
				$html .= $this->makeOptionGroup($childKey, $value);
			}
			else {
				$html .= $this->makeOption($childKey, $value);
			}
		}
		
		if ($key !== null) {
			$html .= "</optgroup>\n";
		}
		
		return $html;
	}
	
	/**
	 * Makes the html for an option.
	 * 
	 * @param	string		$key
	 * @param	string		$value
	 * @return	string				html code of an option tag
	 */
	protected function makeOption($key, $value) {
		$value = $this->encodeHTML($value);
		return '<option label="'.$value.'" value="'.$this->encodeHTML($key).'"'.(in_array($key, $this->selected) ? ' selected="selected"' : '').'>'.$value."</option>\n";
	}
}

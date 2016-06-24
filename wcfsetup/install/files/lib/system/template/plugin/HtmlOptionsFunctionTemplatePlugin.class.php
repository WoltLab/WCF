<?php
namespace wcf\system\template\plugin;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * Template function plugin which generates the options of an html select list.
 * 
 * Usage:
 * 	{htmlOptions options=$array}
 * 	{htmlOptions options=$array selected=$foo}
 * 	{htmlOptions options=$array name="x"}
 * 	{htmlOptions output=$outputArray}
 * 	{htmlOptions output=$outputArray values=$valueArray}
 * 	{htmlOptions object=$databaseObjectList}
 * 	{htmlOptions object=$databaseObjectList selected=$foo}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class HtmlOptionsFunctionTemplatePlugin extends HtmlCheckboxesFunctionTemplatePlugin {
	/**
	 * selected values
	 * @var	string[]
	 */
	protected $selected = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (isset($tagArgs['object']) && ($tagArgs['object'] instanceof DatabaseObjectList)) {
			$tagArgs['options'] = $tagArgs['object'];
		}
		else if (isset($tagArgs['output']) && is_array($tagArgs['output'])) {
			if (count($tagArgs['output'])) {
				if (isset($tagArgs['values']) && is_array($tagArgs['values'])) {
					if (count($tagArgs['output']) == count($tagArgs['values'])) {
						$tagArgs['options'] = array_combine($tagArgs['values'], $tagArgs['output']);
					}
					else {
						$tagArgs['options'] = [];
					}
				}
				else {
					$tagArgs['options'] = array_combine($tagArgs['output'], $tagArgs['output']);
				}
			}
			else {
				$tagArgs['options'] = [];
			}
		}
		
		if (!isset($tagArgs['options']) || (!is_array($tagArgs['options']) && !($tagArgs['options'] instanceof DatabaseObjectList))) {
			throw new SystemException("missing 'options' or 'object' argument in htmlOptions tag");
		}
		
		if (isset($tagArgs['disableEncoding']) && $tagArgs['disableEncoding']) {
			$this->disableEncoding = true;
		}
		else {
			$this->disableEncoding = false;
		}
		
		// get selected values
		$this->selected = [];
		if (isset($tagArgs['selected'])) {
			$this->selected = $tagArgs['selected'];
			if (!is_array($this->selected)) $this->selected = [$this->selected];
		}
		
		// create option list
		$htmloptions = $this->makeOptionGroup(null, $tagArgs['options']);
		
		// create also a 'select' tag
		if (isset($tagArgs['name'])) {
			// unset all system vars
			unset($tagArgs['object'], $tagArgs['options'], $tagArgs['selected'], $tagArgs['output'], $tagArgs['values'], $tagArgs['disableEncoding']);
			
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
	 * Makes the HTML for an option group.
	 * 
	 * @param	string		$key
	 * @param	array		$values
	 * @return	string
	 */
	protected function makeOptionGroup($key, $values) {
		$html = '';
		if ($key !== null) {
			$html = '<optgroup label="'.$this->encodeHTML($key).'">'."\n";
		}
		
		if ($values instanceof DatabaseObjectList) {
			foreach ($values as $childKey => $value) {
				$html .= $this->makeOption($childKey, $value);
			}
		}
		else {
			foreach ($values as $childKey => $value) {
				if (is_array($value)) {
					$html .= $this->makeOptionGroup($childKey, $value);
				}
				else {
					$html .= $this->makeOption($childKey, $value);
				}
			}
		}
		
		if ($key !== null) {
			$html .= "</optgroup>\n";
		}
		
		return $html;
	}
	
	/**
	 * Makes the HTML code for an option.
	 * 
	 * @param	string		$key
	 * @param	string		$value
	 * @return	string
	 */
	protected function makeOption($key, $value) {
		$value = $this->encodeHTML($value);
		return '<option label="'.$value.'" value="'.$this->encodeHTML($key).'"'.(in_array($key, $this->selected) ? ' selected' : '').'>'.$value."</option>\n";
	}
}

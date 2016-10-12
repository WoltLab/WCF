<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * Template function plugin which cycles through given values.
 * 
 * Usage:
 * 	{cycle values="#eee,#fff"}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class CycleFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * cycle data
	 * @var	array
	 */
	protected $cycles = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// get params
		$name = isset($tagArgs['name']) ? $tagArgs['name'] : 'default';
		$print = isset($tagArgs['print']) ? $tagArgs['print'] : 1;
		$advance = isset($tagArgs['advance']) ? $tagArgs['advance'] : 1;
		$reset = isset($tagArgs['reset']) ? $tagArgs['reset'] : 0;
		
		// get values
		if (!isset($tagArgs['values']) ) {
			if (!isset($this->cycles[$name]['values'])) {
				throw new SystemException("missing 'values' argument in cycle tag");
			}
		}
		else {
			if (isset($this->cycles[$name]['values']) && $this->cycles[$name]['values'] != $tagArgs['values'] ) {
				$this->cycles[$name]['index'] = 0;
			}
			$this->cycles[$name]['values'] = $tagArgs['values'];
		}
		
		// get delimiter
		if (!isset($this->cycles[$name]['delimiter'])) {
			// set default delimiter
			$this->cycles[$name]['delimiter'] = ',';
		}
		if (isset($tagArgs['delimiter'])) {
			$this->cycles[$name]['delimiter'] = $tagArgs['delimiter'];
		}
		
		// split values
		if (is_array($this->cycles[$name]['values'])) {
			$cycleArray = $this->cycles[$name]['values'];
		}
		else {
			$cycleArray = explode($this->cycles[$name]['delimiter'], $this->cycles[$name]['values']);
		}
		
		// set index
		if (!isset($this->cycles[$name]['index']) || $reset) {
			$this->cycles[$name]['index'] = 0;
		}
		
		// get result
		$result = $cycleArray[$this->cycles[$name]['index']];
		
		// assign result to template var
		if (isset($tagArgs['assign'])) {
			$print = false;
			$tplObj->assign($tagArgs['assign'], $result);
		}
		
		// update index
		if ($advance) {
			if ($this->cycles[$name]['index'] >= count($cycleArray) - 1) {
				$this->cycles[$name]['index'] = 0;
			}
			else {
				$this->cycles[$name]['index']++;
			}
		}
		
		// print var
		if ($print) {
			return $result;
		}
	}
}

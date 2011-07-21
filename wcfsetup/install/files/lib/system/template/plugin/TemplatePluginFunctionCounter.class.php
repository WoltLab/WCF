<?php
namespace wcf\system\template\plugin;
use wcf\system\template\ITemplatePluginFunction;
use wcf\system\template\TemplateEngine;

/**
 * The 'counter' template function is used to print out a count.
 * 
 * Usage:
 * {counter assign=i}
 * {counter start=10 skip=2}
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginFunctionCounter implements ITemplatePluginFunction {
	protected $counters = array();
	
	/**
	 * @see wcf\system\template\ITemplatePluginFunction::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (!isset($tagArgs['name'])) {
			$tagArgs['name'] = 'default';
		}

		if (!isset($this->counters[$tagArgs['name']])) {
			$this->counters[$tagArgs['name']] = array(
				'start' => 1,
				'skip' => 1,
				'direction' => 'up',
				'count' => 1
			);
		}

		$counter =& $this->counters[$tagArgs['name']];

		if (isset($tagArgs['start'])) {
			$counter['start'] = $counter['count'] = intval($tagArgs['start']);
		}

		if (isset($tagArgs['assign']) && !empty($tagArgs['assign'])) {
			$counter['assign'] = $tagArgs['assign'];
		}

		if (isset($counter['assign'])) {
			$tplObj->assign($counter['assign'], $counter['count']);
		}

		$result = null;
		if (!isset($tagArgs['print']) || $tagArgs['print']) {
			$result = $counter['count'];
		} 
		
		if (isset($tagArgs['skip'])) {
			$counter['skip'] = intval($tagArgs['skip']);
		}

		// get direction
		if (isset($tagArgs['direction'])) {
			$counter['direction'] = $tagArgs['direction'];
		}

    		if ($counter['direction'] == 'down') {
			$counter['count'] -= $counter['skip'];
    		}
		else {
			$counter['count'] += $counter['skip'];
		}

		return $result;
	}
}

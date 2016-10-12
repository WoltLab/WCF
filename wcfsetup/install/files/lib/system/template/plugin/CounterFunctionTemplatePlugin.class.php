<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Template function plugin which is used to print out a count.
 * 
 * Usage:
 * 	{counter assign=i}
 * 	{counter start=10 skip=2}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class CounterFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * counter data
	 * @var	array
	 */
	protected $counters = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (!isset($tagArgs['name'])) {
			$tagArgs['name'] = 'default';
		}
		
		if (!isset($this->counters[$tagArgs['name']])) {
			$this->counters[$tagArgs['name']] = [
				'skip' => isset($tagArgs['skip']) ? $tagArgs['skip'] : 1,
				'direction' => isset($tagArgs['direction']) ? $tagArgs['direction'] : 'up',
				'assign' => (isset($tagArgs['assign']) && !empty($tagArgs['assign'])) ? $tagArgs['assign'] : null,
				'print' => isset($tagArgs['print']) ? $tagArgs['print'] : false,
				'count' => isset($tagArgs['start']) ? $tagArgs['start'] : 1
			];
		}
		
		$counter =& $this->counters[$tagArgs['name']];
		
		if ($counter['assign'] !== null) {
			$tplObj->assign($counter['assign'], $counter['count']);
		}
		
		$result = '';
		if ($counter['print']) {
			$result = $counter['count'];
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

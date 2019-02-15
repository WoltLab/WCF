<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;

/**
 * Template modifier plugin which calculates the difference between two unix timestamps
 * and returns it as a textual date interval.
 * Compared to `DateDiffModifierTemplatePlugin`, this plugin allows a cleaner syntax
 * and offers more features.
 *
 * Usage:
 *	{dateInterval start=$startTimestamp end=$endTimestamp full=true format='sentence'}
 * 
 * Parameters:
 * 	- `start` refers to the start of the time interval, defaults to the current time
 * 	- `end` refers to the end of the time interval, default to the current time
 * 	  (though either `start` or `end` has to be set)
 * 	- `full` determines if the full difference down to minutes (`true`) will be
 *        shown or just the longest time interval type, defaults to `false`
 * 	- `format` determines how the output is formatted, see `DateUtil::FORMAT_*`
 * 	   constants, defaults to `default` 
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	3.1
 */
class DateIntervalFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// read start and end time, each defaulting to current time
		$start = $end = TIME_NOW;
		
		if (!isset($tagArgs['start']) && !isset($tagArgs['end'])) {
			throw new \InvalidArgumentException("Neither a 'start' nor an 'end' argument has been provided.");
		}
		
		if (isset($tagArgs['start'])) {
			$start = intval($tagArgs['start']);
		}
		if (isset($tagArgs['end'])) {
			$end = intval($tagArgs['end']);
		}
		
		$startTime = DateUtil::getDateTimeByTimestamp($start);
		$endTime = DateUtil::getDateTimeByTimestamp($end);
		
		// read `full` flag for output precision
		$fullInterval = false;
		if (!empty($tagArgs['full'])) {
			$fullInterval = true;
		}
		
		// read output format
		$formatType = DateUtil::FORMAT_DEFAULT;
		if (isset($tagArgs['format'])) {
			$constant = DateUtil::class .'::FORMAT_'. strtoupper($tagArgs['format']);
			if (!defined($constant)) {
				throw new \InvalidArgumentException("Invalid format '{$tagArgs['format']}' provided.");
			}
			
			$formatType = constant($constant);
		}
		
		return DateUtil::formatInterval($endTime->diff($startTime), $fullInterval, $formatType);
	}
}

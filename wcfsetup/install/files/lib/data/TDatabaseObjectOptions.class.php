<?php
namespace wcf\data;

/**
 * Provides a method for validating database object options.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 */
trait TDatabaseObjectOptions {
	/**
	 * Returns true if at least one of the options required by this object is set.
	 * 
	 * @return	boolean
	 */
	public function validateOptions() {
		if ($this->options) {
			$options = explode(',', strtoupper($this->options));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					return true;
				}
			}
			
			return false;
		}
		
		return true;
	}
}

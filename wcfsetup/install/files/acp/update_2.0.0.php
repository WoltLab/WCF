<?php
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */

// remove log files used during Beta/RC
@unlink(WCF_DIR.'__installPerformance.log');
@unlink(WCF_DIR.'__wcfSetupPerformance.log');

<?php
namespace wcf\system\option;

/**
 * Every option type whose values can only be selected from a pre-defined list of
 * options has to implemenent this interface.
 *
 * The pre-defined list of options has to be available via the option database
 * object's `selectOption` property.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 * @since	3.2
 */
interface ISelectOptionOptionType {}

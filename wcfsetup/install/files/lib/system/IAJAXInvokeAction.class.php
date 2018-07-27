<?php
namespace wcf\system;

/**
 * Default interface for AJAX-based method calls.
 * 
 * You SHOULD NOT implement this interface in generic classes, as each method is entirely
 * responsible to verify parameters and permissions. Implementing this class in generic
 * classes leads to a potential breach of security and unforeseen side-effects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System
 */
interface IAJAXInvokeAction { }

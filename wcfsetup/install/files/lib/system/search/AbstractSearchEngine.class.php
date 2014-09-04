<?php
namespace wcf\system\search;
use wcf\system\SingletonFactory;

/**
 * Default implementation for search engines, this class should be extended by
 * all search engines to preserve compatibility in case of interface changes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search
 * @category	Community Framework
 */
abstract class AbstractSearchEngine extends SingletonFactory implements ISearchEngine { }

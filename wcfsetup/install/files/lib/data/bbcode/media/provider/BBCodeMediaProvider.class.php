<?php
namespace wcf\data\bbcode\media\provider;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\BBCodeMediaProviderCacheBuilder;
use wcf\system\request\IRouteController;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Represents a BBCode media provider.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode\Media\Provider
 *
 * @property-read	integer		$providerID
 * @property-read	string		$title
 * @property-read	string		$regex
 * @property-read	string		$html
 */
class BBCodeMediaProvider extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'bbcode_media_provider';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'providerID';
	
	/**
	 * cached providers
	 * @var	BBCodeMediaProvider[]
	 */
	protected static $cache = null;
	
	/**
	 * Loads the provider cache.
	 * 
	 * @return	BBCodeMediaProvider[]
	 */
	public static function getCache() {
		if (self::$cache === null) {
			self::$cache = BBCodeMediaProviderCacheBuilder::getInstance()->getData();
		}
		
		return self::$cache;
	}
	
	/**
	 * Returns true if given URL is a media URL.
	 * 
	 * @param	string		$url
	 * @return	boolean
	 */
	public static function isMediaURL($url) {
		foreach (static::getCache() as $provider) {
			if ($provider->matches($url)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Checks whether this provider matches the given URL.
	 * 
	 * @param	string		$url
	 * @return	boolean
	 */
	public function matches($url) {
		$lines = explode("\n", StringUtil::unifyNewlines($this->regex));
		
		foreach ($lines as $line) {
			if (Regex::compile($line)->match($url)) return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the html for this provider.
	 * 
	 * @param	string		$url
	 * @return	string
	 */
	public function getOutput($url) {
		$lines = explode("\n", StringUtil::unifyNewlines($this->regex));
		
		foreach ($lines as $line) {
			$regex = new Regex($line);
			if (!$regex->match($url)) continue;
			
			$output = $this->html;
			foreach ($regex->getMatches() as $name => $value) {
				$output = str_replace('{$'.$name.'}', $value, $output);
			}
			return $output;
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
}

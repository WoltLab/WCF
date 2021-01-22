<?php
namespace wcf\data\bbcode\media\provider;
use wcf\data\DatabaseObject;
use wcf\system\bbcode\media\provider\IBBCodeMediaProvider;
use wcf\system\cache\builder\BBCodeMediaProviderCacheBuilder;
use wcf\system\request\IRouteController;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Represents a BBCode media provider.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Bbcode\Media\Provider
 *
 * @property-read	int		$providerID	unique id of the bbcode media provider
 * @property-read	string		$title		title of the bbcode media provider (shown in acp)
 * @property-read	string		$regex		regular expression to recognize media elements/element urls
 * @property-read	string		$html		html code used to render media elements
 * @property-read	string		$className	callback class name                            
 */
class BBCodeMediaProvider extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'bbcode_media_provider';
	
	/**
	 * cached providers
	 * @var	BBCodeMediaProvider[]
	 */
	protected static $cache = null;
	
	/**
	 * media provider callback instance
	 * @var IBBCodeMediaProvider
	 */
	protected $callback;
	
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
	 * @return	bool
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
	 * @return	bool
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
			
			if ($this->getCallback() !== null) {
				return $this->getOutputForUserConsent($url, $this->getCallback()->parse($url, $regex->getMatches()));
			}
			else {
				$output = $this->html;
				foreach ($regex->getMatches() as $name => $value) {
					$output = str_replace('{$' . $name . '}', $value, $output);
				}
				return $this->getOutputForUserConsent($url, $output);
			}
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Returns media provider callback instance.
	 * 
	 * @return      IBBCodeMediaProvider
	 */
	public function getCallback() {
		if (!$this->className) return null;
		
		if ($this->callback === null) {
			$this->callback = new $this->className;
		}
		
		return $this->callback;
	}
	
	/**
	 * Replaces embedded media with an approval dialog.
	 * 
	 * @param string $url
	 * @param string $html
	 * @return string
	 */
	protected function getOutputForUserConsent($url, $html) {
		if (!MESSAGE_ENABLE_USER_CONSENT) {
			return $html;
		}
		
		if (WCF::getUser()->userID && WCF::getUser()->getUserOption('enableEmbeddedMedia')) {
			return $html;
		}
		
		return WCF::getTPL()->fetch('messageUserConsent', 'wcf', [
			'host' => Url::parse($url)['host'],
			'payload' => base64_encode($html),
			'url' => $url,
		]);
	}
}

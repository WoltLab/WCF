<?php
namespace wcf\util;

/**
 * Generic wrapper around `parse_url()`.
 * 
 * Unlike the base function that is used during processing, the method `Url::parse()`
 * will always provide a sane list of components, regardless if they're provided in
 * the `parse_url()`-output. You'll still need to check if the desired parameters
 * are non-empty.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 * @since       3.1
 */
final class Url implements \ArrayAccess {
	/**
	 * list of url components
	 * @var string[]
	 */
	private $components = [];
	
	/**
	 * maps properties to the array indices
	 * @var integer[]
	 */
	private static $propertyMap = [
		PHP_URL_SCHEME => 'scheme',
		PHP_URL_HOST => 'host',
		PHP_URL_PORT => 'port',
		PHP_URL_USER => 'user',
		PHP_URL_PASS => 'pass',
		PHP_URL_PATH => 'path',
		PHP_URL_QUERY => 'query',
		PHP_URL_FRAGMENT => 'fragment'
	];
	
	/**
	 * Tests if provided $url appears to be an URL.
	 * 
	 * This method is a wrapper around filter_var with FILTER_VALIDATE_URL.
	 * 
	 * @param       string          $url
	 * @return      boolean
	 */
	public static function is($url) {
		return filter_var($url, FILTER_VALIDATE_URL) !== false;
	}
	
	/**
	 * Parses the provided url and returns an array containing all possible url
	 * components, even those not originally present, but in that case set to am
	 * 'empty' value.
	 * 
	 * @param       string          $url
	 * @return      Url
	 */
	public static function parse($url) {
		$url = parse_url($url);
		if ($url === false) $url = [];
		
		return new self([
			'scheme' => (isset($url['scheme'])) ? $url['scheme'] : '',
			'host' => (isset($url['host'])) ? $url['host'] : '',
			'port' => (isset($url['port'])) ? $url['port'] : 0,
			'user' => (isset($url['user'])) ? $url['user'] : '',
			'pass' => (isset($url['pass'])) ? $url['pass'] : '',
			'path' => (isset($url['path'])) ? $url['path'] : '',
			'query' => (isset($url['query'])) ? $url['query'] : '',
			'fragment' => (isset($url['fragment'])) ? $url['fragment'] : ''
		]);
	}
	
	/**
	 * Returns true if the provided url contains all listed components and
	 * that they're non-empty.
	 * 
	 * @param       string          $url
	 * @param       integer[]       $components
	 * @return      boolean
	 */
	public static function contains($url, array $components) {
		$result = self::parse($url);
		foreach ($components as $component) {
			if (empty($result[$component])) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Url constructor, object creation is only allowed through `Url::parse()`.
	 * 
	 * @param       string[]        $components
	 */
	private function __construct(array $components) {
		$this->components = $components;
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset) {
		// We're throwing an exception here, if `$offset` is an unknown property
		// key, which is a bit weird when working with `isset()` or `empty()`,
		// but any unknown key is a guaranteed programming error.
		// 
		// On top of that, we'll only return true, if the value is actually non-
		// empty. That doesn't make much sense in combination with `isset()`, but
		// instead is used to mimic the legacy behavior of the array returned by
		// `parse_url()` with its missing keys. 
		return !empty($this->components[$this->getIndex($offset)]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset) {
		return $this->components[$this->getIndex($offset)];
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset) {
		throw new \RuntimeException("Url components are immutable");
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value) {
		throw new \RuntimeException("Url components are immutable");
	}
	
	/**
	 * Attempts to resolve string properties and maps them to their integer-based
	 * component indices. Will throw an exception if the property is unknown,
	 * making it easier to spot typos.
	 * 
	 * @param       mixed   $property
	 * @return      integer
	 * @throws      \RuntimeException
	 */
	private function getIndex($property) {
		if (is_int($property) && isset(self::$propertyMap[$property])) {
			return self::$propertyMap[$property];
		}
		else if (is_string($property) && isset($this->components[$property])) {
			return $property;
		}
		
		throw new \RuntimeException("Unknown url component offset '" . $property . "'.");
	}
}

<?php
namespace wcf\system\database;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Wrapper around the \Redis class of php redis.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database
 * 
 * @method	integer		del($key1, $key2 = null, $key3 = null)
 * @method	boolean		expire($key, $ttl)
 * @method	string|bool	get($key)
 * @method	integer		hDel($key, $hashKey1, $hashKey2 = null, $hashKeyN = null)
 * @method	string		hGet($key, $hashKey)
 * @method	integer		hSet($key, $hashKey, $value)
 * @method	integer		incr($key)
 * @method	string		info($option = null)
 * @method	boolean		setex($key, $ttl, $value)
 * @method	boolean		setnx($key, $value)
 * @method	integer		ttl($key)
 */
class Redis {
	/**
	 * wrapped redis connection
	 * @var	\Redis
	 */
	protected $redis = null;
	
	/**
	 * DSN string used to connect.
	 * @var	string
	 */
	protected $dsn = '';
	
	/**
	 * Connects to the redis server given by the DSN.
	 * 
	 * @param	string		$dsn
	 */
	public function __construct($dsn) {
		if (!class_exists('Redis')) {
			throw new \BadMethodCallException('Redis support is not enabled.');
		}
		
		$this->dsn = $dsn;
		
		$this->redis = new \Redis();
		
		$regex = new Regex('^\[([a-z0-9\:\.]+)\](?::([0-9]{1,5}))?$', Regex::CASE_INSENSITIVE);
		$host = StringUtil::trim($this->dsn);
		$port = 6379; // default Redis port
		
		// check for IPv6
		if ($regex->match($host)) {
			$matches = $regex->getMatches();
			$host = $matches[1];
			
			if (isset($matches[2])) {
				$port = $matches[2];
			}
		}
		else {
			// IPv4 or host, try to get port
			if (strpos($host, ':')) {
				$parsedHost = explode(':', $host);
				$host = $parsedHost[0];
				$port = $parsedHost[1];
			}
		}
		
		if (!$this->redis->connect($host, $port)) {
			throw new \RuntimeException('Unable to connect to Redis server');
		}
		
		// automatically prefix key names with the WCF UUID
		$this->redis->setOption(\Redis::OPT_PREFIX, WCF_UUID.':');
	}
	
	/**
	 * Passes all method calls down to the underlying Redis connection.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public function __call($name, array $arguments) {
		switch ($name) {
			case 'setOption':
			case 'getOption':
			
			case 'open':
			case 'connect':
			
			case 'popen':
			case 'pconnect':
			
			case 'auth':
			
			case 'select':
			
			case 'close':
				throw new \BadMethodCallException('You must not use '.$name);
		}
		return call_user_func_array([$this->redis, $name], $arguments);
	}
	
	/**
	 * Returns a new, raw, redis instance to the same server.
	 *
	 * @return	\Redis
	 */
	public function unwrap() {
		return (new self($this->dsn))->redis;
	}
}

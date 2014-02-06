<?php
namespace wcf\util;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPServerErrorException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Sends HTTP requests.
 * It supports POST, SSL, Basic Auth etc.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class HTTPRequest {
	/**
	 * given options
	 * @var	array
	 */
	private $options = array();
	
	/**
	 * given post parameters
	 * @var	array
	 */
	private $postParameters = array();
	
	/**
	 * given files
	 * @var	array
	 */
	private $files = array();
	
	/**
	 * indicates if request will be made via SSL
	 * @var	boolean
	 */
	private $useSSL = false;
	
	/**
	 * target host
	 * @var	string
	 */
	private $host;
	
	/**
	 * target port
	 * @var	integer
	 */
	private $port;
	
	/**
	 * target path
	 * @var	string
	 */
	private $path;
	
	/**
	 * target query string
	 * @var	string
	 */
	private $query;
	
	/**
	 * request URL
	 * @var	string
	 */
	private $url = '';
	
	/**
	 * request headers
	 * @var	array<string>
	 */
	private $headers = array();
	
	/**
	 * request body
	 * @var	string
	 */
	private $body = '';
	
	/**
	 * reply headers
	 * @var	array<string>
	 */
	private $replyHeaders = array();
	
	/**
	 * reply body
	 * @var	string
	 */
	private $replyBody = '';
	
	/**
	 * reply status code
	 * @var	integer
	 */
	private $statusCode = 0;
	
	/**
	 * Constructs a new instance of HTTPRequest.
	 * 
	 * @param	string		$url		URL to connect to
	 * @param	array<string>	$options
	 * @param	mixed		$postParameters	Parameters to send via POST
	 * @param	array		$files		Files to attach to the request
	 */
	public function __construct($url, array $options = array(), $postParameters = array(), array $files = array()) {
		$this->setURL($url);
		
		$this->postParameters = $postParameters;
		$this->files = $files;
		
		$this->setOptions($options);
		
		// set default headers
		$this->addHeader('User-Agent', "HTTP.PHP (HTTPRequest.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->languageCode.")");
		$this->addHeader('Accept', '*/*');
		$this->addHeader('Accept-Language', WCF::getLanguage()->getFixedLanguageCode());
		if ($this->options['method'] !== 'GET') {
			if (empty($this->files)) {
				if (is_array($postParameters)) {
					$this->body = http_build_query($this->postParameters, '', '&');
				}
				else if (is_string($postParameters) && !empty($postParameters)) {
					$this->body = $postParameters;
				}
				
				$this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
			}
			else {
				$boundary = StringUtil::getRandomID();
				$this->addHeader('Content-Type', 'multipart/form-data; boundary='.$boundary);
				
				// source of the iterators: http://stackoverflow.com/a/7623716/782822
				if (!empty($this->postParameters)) {
					$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->postParameters), \RecursiveIteratorIterator::SELF_FIRST);
					foreach ($iterator as $k => $v) {
						if (!$iterator->hasChildren()) {
							$key = '';
							for ($i = 0, $max = $iterator->getDepth(); $i <= $max; $i++) {
								if ($i === 0) $key .= $iterator->getSubIterator($i)->key();
								else $key .= '['.$iterator->getSubIterator($i)->key().']';
							}
							
							$this->body .= "--".$boundary."\r\n";
							$this->body .= 'Content-Disposition: form-data; name="'.$key.'"'."\r\n\r\n";
							$this->body .= $v."\r\n";
						}
					}
				}
				
				$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->files), \RecursiveIteratorIterator::SELF_FIRST);
				foreach ($iterator as $k => $v) {
					if (!$iterator->hasChildren()) {
						$key = '';
						for ($i = 0, $max = $iterator->getDepth(); $i <= $max; $i++) {
							if ($i === 0) $key .= $iterator->getSubIterator($i)->key();
							else $key .= '['.$iterator->getSubIterator($i)->key().']';
						}
						
						$this->body .= "--".$boundary."\r\n";
						$this->body .= 'Content-Disposition: form-data; name="'.$k.'"; filename="'.basename($v).'"'."\r\n";
						$this->body .= 'Content-Type: '.(FileUtil::getMimeType($v) ?: 'application/octet-stream.')."\r\n\r\n";
						$this->body .= file_get_contents($v)."\r\n";
					}
				}
				
				$this->body .= "--".$boundary."--";
			}
			$this->addHeader('Content-length', strlen($this->body));
		}
		if (isset($this->options['auth'])) {
			$this->addHeader('Authorization', "Basic ".base64_encode($options['auth']['username'].":".$options['auth']['password']));
		}
		$this->addHeader('Host', $this->host.($this->port != ($this->useSSL ? 443 : 80) ? ':'.$this->port : ''));
		$this->addHeader('Connection', 'Close');
	}
	
	/**
	 * Parses the given URL and applies PROXY_SERVER_HTTP.
	 * 
	 * @param	string		$url
	 */
	private function setURL($url) {
		if (PROXY_SERVER_HTTP) {
			$parsedUrl = parse_url(PROXY_SERVER_HTTP);
			$this->path = $url;
		}
		else {
			$parsedUrl = parse_url($url);
			$this->path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
		}
		
		$this->useSSL = $parsedUrl['scheme'] === 'https';
		$this->host = $parsedUrl['host'];
		$this->port = isset($parsedUrl['port']) ? $parsedUrl['port'] : ($this->useSSL ? 443 : 80);
		$this->query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
		
		// update the 'Host:' header if URL has changed
		if (!empty($this->url) && $this->url != $url) {
			$this->addHeader('Host', $this->host.($this->port != ($this->useSSL ? 443 : 80) ? ':'.$this->port : ''));
		}
		
		$this->url = $url;
	}
	
	/**
	 * Executes the HTTP request.
	 */
	public function execute() {
		// connect
		$remoteFile = new RemoteFile(($this->useSSL ? 'ssl://' : '').$this->host, $this->port, $this->options['timeout']);
		
		$request = $this->options['method']." ".$this->path.($this->query ? '?'.$this->query : '')." HTTP/1.0\r\n";
		
		// add headers
		foreach ($this->headers as $name => $values) {
			foreach ($values as $value) {
				$request .= $name.": ".$value."\r\n";
			}
		}
		$request .= "\r\n";
		// add post parameters
		if ($this->options['method'] !== 'GET') $request .= $this->body."\r\n\r\n";
		
		$remoteFile->puts($request);
		
		$inHeader = true;
		$this->replyHeaders = array();
		$this->replyBody = '';
		
		// read http response.
		while (!$remoteFile->eof()) {
			$line = $remoteFile->gets();
			if ($inHeader) {
				if (rtrim($line) === '') {
					$inHeader = false;
					continue;
				}
				$this->replyHeaders[] = $line;
			}
			else {
				$this->replyBody .= $line;
			}
		}
		
		$this->parseReply();
	}
	
	/**
	 * Parses the reply.
	 */
	private function parseReply() {
		$headers = array();
		
		foreach ($this->replyHeaders as $header) {
			if (strpos($header, ':') === false) {
				$headers[trim($header)] = trim($header);
				continue;
			}
			list($key, $value) = explode(':', $header, 2);
			$headers[$key] = trim($value);
		}
		$this->replyHeaders = $headers;
		
		// get status code
		$statusLine = reset($this->replyHeaders);
		$regex = new Regex('^HTTP/1.[01] (\d{3})');
		if (!$regex->match($statusLine)) throw new SystemException("Unexpected status '".$statusLine."'");
		$matches = $regex->getMatches();
		$this->statusCode = $matches[1];
		
		// validate length
		if (isset($this->replyHeaders['Content-Length'])) {
			if (strlen($this->replyBody) != $this->replyHeaders['Content-Length']) {
				throw new SystemException('Body length does not match length given in header');
			}
		}
		
		// validate status code
		switch ($this->statusCode) {
			case '301':
			case '302':
			case '303':
			case '307':
				// redirect
				if ($this->options['maxDepth'] <= 0) throw new SystemException("Received status code '".$this->statusCode."' from server, but recursion level is exhausted");
				
				$newRequest = clone $this;
				$newRequest->options['maxDepth']--;
				
				// The response to the request can be found under a different URI and SHOULD
				// be retrieved using a GET method on that resource.
				// http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.3.4
				if ($this->statusCode == '303') {
					$newRequest->options['method'] = 'GET';
					$newRequest->postParameters = array();
					$newRequest->addHeader('Content-length', '');
					$newRequest->addHeader('Content-Type', '');
				}
				
				try {
					$newRequest->setURL($this->replyHeaders['Location']);
				}
				catch (SystemException $e) {
					throw new SystemException("Received 'Location: ".$this->replyHeaders['Location']."' from server, which is invalid.", 0, $e);
				}
				
				try {
					$newRequest->execute();
					
					// update data with data from the inner request
					$this->url = $newRequest->url;
					$this->statusCode = $newRequest->statusCode;
					$this->replyHeaders = $newRequest->replyHeaders;
					$this->replyBody = $newRequest->replyBody;
				}
				catch (SystemException $e) {
					// update data with data from the inner request
					$this->url = $newRequest->url;
					$this->statusCode = $newRequest->statusCode;
					$this->replyHeaders = $newRequest->replyHeaders;
					$this->replyBody = $newRequest->replyBody;
					
					throw $e;
				}
				
				return;
			break;
			
			case '200':
			case '204':
				// we are fine
			break;
			
			case '401':
			case '403':
				throw new HTTPUnauthorizedException("Received status code '".$this->statusCode."' from server");
			break;
			
			case '404':
				throw new HTTPNotFoundException("Received status code '404' from server");
			break;
			
			case '500':
				throw new HTTPServerErrorException("Received status code '500' from server");
			break;
			
			default:
				throw new SystemException("Received unhandled status code '".$this->statusCode."' from server");
			break;
		}
	}
	
	/**
	 * Returns an array with the replied data.
	 * 
	 * @return	array
	 */
	public function getReply() {
		return array(
			'statusCode' => $this->statusCode, 
			'headers' => $this->replyHeaders, 
			'body' => $this->replyBody,
			'url' => $this->url
		);
	}
	
	/**
	 * Sets options and applies default values when an option is omitted.
	 * 
	 * @param	array		$options
	 */
	private function setOptions(array $options) {
		if (!isset($options['timeout'])) {
			$options['timeout'] = 10;
		}
		
		if (!isset($options['method'])) {
			$options['method'] = (!empty($this->postParameters) || !empty($this->files) ? 'POST' : 'GET');
		}
		
		if (!isset($options['maxDepth'])) {
			$options['maxDepth'] = 2;
		}
		
		if (isset($options['auth'])) {
			if (!isset($options['auth']['username'])) {
				throw new SystemException('Username is missing in authentification data.');
			}
			if (!isset($options['auth']['password'])) {
				throw new SystemException('Password is missing in authentification data.');
			}
		}
		
		$this->options = $options;
	}
	
	/**
	 * Adds a header to this request.
	 * When an empty value is given existing headers of this name will be removed. When append
	 * is set to false existing values will be overwritten.
	 * 
	 * @param	string		$name
	 * @param	string		$value
	 * @param	boolean		$append
	 */
	public function addHeader($name, $value, $append = false) {
		if ($value === '') {
			unset($this->headers[$name]);
			return;
		}
		
		if ($append && isset($this->headers[$name])) {
			$this->headers[$name][] = $value;
		}
		else {
			$this->headers[$name] = array($value);
		}
	}
	
	/**
	 * Resets reply data when cloning.
	 */
	private function __clone() {
		$this->replyHeaders = array();
		$this->replyBody = '';
		$this->statusCode = 0;
	}
}

<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Sends HTTP requests.
 * It supports POST, SSL, Basic Auth etc.
 * 
 * @author	Tim Düsterhus
 * @copyright	2001-2012 WoltLab GmbH
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
	 * indicates if request was made via SSL
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
	 * request headers
	 * @var	array<string>
	 */
	private $headers = array();
	
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
	 * @param	array		$postParameters	Parameters to send via POST
	 */
	public function __construct($url, array $options = array(), array $postParameters = array()) {
		$this->setURL($url);
		
		$this->postParameters = $postParameters;
		
		$this->setOptions($options);
		
		// set default headers
		$this->addHeader('User-Agent', "HTTP.PHP (HTTPRequest.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->languageCode.")");
		$this->addHeader('Accept', '*/*');
		$this->addHeader('Accept-Language', WCF::getLanguage()->languageCode);
		if ($this->options['method'] !== 'GET') {
			$this->addHeader('Content-length', strlen(http_build_query($this->postParameters)));
			$this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
		}
		if (isset($this->options['auth'])) {
			$this->addHeader('Authorization', "Basic ".base64_encode($options['auth']['username'].":".$options['auth']['password']));
		}
		$this->addHeader('Host', $this->host);
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
		$this->path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
		$this->query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
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
		if ($this->options['method'] !== 'GET') $request .= http_build_query($this->postParameters)."\r\n\r\n";
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
		
		$statusLine = reset($this->replyHeaders);
		$regex = new Regex('^HTTP/1.0 (\d{3})'); // we expect an HTTP 1.0 response, as we sent an HTTP 1.0 request
		if (!$regex->match($statusLine)) throw new SystemException("Unexpected status '".$statusLine."'");
		$matches = $regex->getMatches();
		$statusCode = $matches[1];
		
		switch ($statusCode) {
			case '301':
			case '302':
			case '303':
			case '307':
				// redirect
				if ($this->options['maxDepth'] <= 0) throw new SystemException("Got redirect status '".$statusCode."', but recursion level is exhausted");
				
				$newRequest = clone $this;
				$newRequest->options['maxDepth']--;
				if ($statusCode != '307') {
					$newRequest->options['method'] = 'GET';
					$newRequest->postParameters = array();
					$newRequest->addHeader('Content-length', '');
					$newRequest->addHeader('Content-Type', '');
				}
				try {
					$newRequest->setURL($this->replyHeaders['Location']);
				}
				catch (SystemException $e) {
					throw new SystemException("Given redirect URL '".$this->replyHeaders['Location']."' is invalid. Probably the host is missing?", 0, $e);
				}
				$newRequest->execute();
				
				$this->statusCode = $newRequest->statusCode;
				$this->replyHeaders = $newRequest->replyHeaders;
				$this->replyBody = $newRequest->replyBody;
				return;
			break;
			
			case '200':
			case '204':
				// we are fine
			break;
			
			default:
				throw new SystemException("Got status '".$statusCode."' and I don't know how to handle it");
			break;
		}
		
		// validate length
		if (isset($this->replyHeaders['Content-Length'])) {
			if (strlen($this->replyBody) != $this->replyHeaders['Content-Length']) {
				throw new SystemException('Body length does not match length given in header');
			}
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
			'body' => $this->replyBody
		);
	}
	
	/**
	 * Sets options and applies default values when an option is omitted.
	 * 
	 * @param	array		$options
	 */
	private function setOptions(array $options) {
		if (!isset($options['timeout'])) {
			$options['timeout'] = 30;
		}
		
		if (!isset($options['method'])) {
			$options['method'] = (!empty($this->postParameters) ? 'POST' : 'GET');
		}
		
		if (!isset($options['maxDepth'])) {
			$options['maxDepth'] = 2;
		}
		
		if (isset($options['auth'])) {
			if (!isset($options['auth']['username'])) {
				throw new SystemException('username is missing in authentification data');
			}
			if (!isset($options['auth']['password'])) {
				throw new SystemException('password is missing in authentification data');
			}
		}
		
		$this->options = $options;
	}
	
	/**
	 * Adds a header to this request.
	 * When an empty value is given existing headers of this name will be remove. When append
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
		
		$this->headers[$name] = (array) $value;
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
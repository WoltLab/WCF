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
 * Sends HTTP/1.1 requests.
 * It supports POST, SSL, Basic Auth etc.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
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
	 * indicates if the connection to the proxy target will be made via SSL
	 * @var	boolean
	 */
	private $originUseSSL = false;
	
	/**
	 * target host
	 * @var	string
	 */
	private $host;
	
	/**
	 * target host if a proxy is used
	 * @var	string
	 */
	private $originHost;
	
	/**
	 * target port
	 * @var	integer
	 */
	private $port;
	
	/**
	 * target port if a proxy is used
	 * @var	integer
	 */
	private $originPort;
	
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
	 * @var	string[][]
	 */
	private $headers = array();
	
	/**
	 * legacy headers
	 * @var	string[]
	 */
	private $legacyHeaders = array();
	
	/**
	 * request body
	 * @var	string
	 */
	private $body = '';
	
	/**
	 * reply headers
	 * @var	string[]
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
	 * @param	string[]	$options
	 * @param	mixed		$postParameters	Parameters to send via POST
	 * @param	array		$files		Files to attach to the request
	 */
	public function __construct($url, array $options = array(), $postParameters = array(), array $files = array()) {
		$this->setURL($url);
		
		$this->postParameters = $postParameters;
		$this->files = $files;
		
		$this->setOptions($options);
		
		// set default headers
		$this->addHeader('user-agent', "HTTP.PHP (HTTPRequest.class.php; WoltLab Community Framework/".WCF_VERSION."; ".WCF::getLanguage()->languageCode.")");
		$this->addHeader('accept', '*/*');
		$this->addHeader('accept-language', WCF::getLanguage()->getFixedLanguageCode());
		
		if (isset($this->options['maxLength'])) {
			$this->addHeader('Range', 'bytes=0-'.($this->options['maxLength'] - 1));
		}
		
		if ($this->options['method'] !== 'GET') {
			if (empty($this->files)) {
				if (is_array($postParameters)) {
					$this->body = http_build_query($this->postParameters, '', '&');
				}
				else if (is_string($postParameters) && !empty($postParameters)) {
					$this->body = $postParameters;
				}
				
				$this->addHeader('content-type', 'application/x-www-form-urlencoded');
			}
			else {
				$boundary = StringUtil::getRandomID();
				$this->addHeader('content-type', 'multipart/form-data; boundary='.$boundary);
				
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
			$this->addHeader('content-length', strlen($this->body));
		}
		if (isset($this->options['auth'])) {
			$this->addHeader('authorization', "Basic ".base64_encode($options['auth']['username'].":".$options['auth']['password']));
		}
		$this->addHeader('connection', 'Close');
	}
	
	/**
	 * Parses the given URL and applies PROXY_SERVER_HTTP.
	 * 
	 * @param	string		$url
	 */
	private function setURL($url) {
		$parsedUrl = $originUrl = parse_url($url);
		
		$this->originUseSSL = $originUrl['scheme'] === 'https';
		$this->originHost = $originUrl['host'];
		$this->originPort = isset($originUrl['port']) ? $originUrl['port'] : ($this->originUseSSL ? 443 : 80);
		
		if (PROXY_SERVER_HTTP && !$this->originUseSSL) {
			$this->path = $url;
			$this->query = '';
		}
		else {
			$this->path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '/';
			$this->query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';
		}
		
		if (PROXY_SERVER_HTTP) {
			$parsedUrl = parse_url(PROXY_SERVER_HTTP);
		}
		
		$this->useSSL = $parsedUrl['scheme'] === 'https';
		$this->host = $parsedUrl['host'];
		$this->port = isset($parsedUrl['port']) ? $parsedUrl['port'] : ($this->useSSL ? 443 : 80);
		
		// update the 'Host:' header if URL has changed
		if ($this->url != $url) {
			$this->addHeader('host', $this->originHost.($this->originPort != ($this->originUseSSL ? 443 : 80) ? ':'.$this->originPort : ''));
		}
		
		$this->url = $url;
	}
	
	/**
	 * Executes the HTTP request.
	 */
	public function execute() {
		// connect
		$remoteFile = new RemoteFile(($this->useSSL ? 'ssl://' : '').$this->host, $this->port, $this->options['timeout'], array(
			'ssl' => array(
				'peer_name' => $this->originHost
			)
		));
		
		if ($this->originUseSSL && PROXY_SERVER_HTTP) {
			if ($this->useSSL) throw new SystemException("Unable to proxy HTTPS when using TLS for proxy connection");
			
			$request = "CONNECT ".$this->originHost.":".$this->originPort." HTTP/1.0\r\n";
			if (isset($this->headers['user-agent'])) {
				$request .= 'user-agent: '.reset($this->headers['user-agent'])."\r\n";
			}
			$request .= "Host: ".$this->originHost.":".$this->originPort."\r\n";
			$request .= "\r\n";
			$remoteFile->puts($request);
			$this->replyHeaders = array();
			while (!$remoteFile->eof()) {
				$line = $remoteFile->gets();
				if (rtrim($line) === '') {
					$this->parseReplyHeaders();
					
					break;
				}
				$this->replyHeaders[] = $line;
			}
			if ($this->statusCode != 200) throw new SystemException("Expected 200 Ok as reply to my CONNECT, got '".$this->statusCode."'");
			$remoteFile->setTLS(true);
		}
		
		$request = $this->options['method']." ".$this->path.($this->query ? '?'.$this->query : '')." HTTP/1.1\r\n";
		
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
		$chunkLength = 0;
		$bodyLength = 0;
		
		$chunkedTransferRegex = new Regex('(^|,)[ \t]*chunked[ \t]*$', Regex::CASE_INSENSITIVE);
		// read http response, until one of is true
		// a) EOF is reached
		// b) bodyLength is at least maxLength
		// c) bodyLength is at least Content-Length
		while (!(
			$remoteFile->eof() ||
			(isset($this->options['maxLength']) && $bodyLength >= $this->options['maxLength']) ||
			(isset($this->replyHeaders['content-length']) && $bodyLength >= end($this->replyHeaders['content-length']))
		)) {
			
			if ($chunkLength) {
				if (isset($this->options['maxLength'])) $chunkLength = min($chunkLength, $this->options['maxLength'] - $bodyLength);
				$line = $remoteFile->read($chunkLength);
			}
			else if (!$inHeader && (!isset($this->replyHeaders['transfer-encoding']) || !$chunkedTransferRegex->match(end($this->replyHeaders['transfer-encoding'])))) {
				$length = 1024;
				if (isset($this->options['maxLength'])) $length = min($length, $this->options['maxLength'] - $bodyLength);
				if (isset($this->replyHeaders['content-length'])) $length = min($length, end($this->replyHeaders['content-length']) - $bodyLength);
				
				$line = $remoteFile->read($length);
			}
			else {
				$line = $remoteFile->gets();
			}
			
			if ($inHeader) {
				if (rtrim($line) === '') {
					$inHeader = false;
					$this->parseReplyHeaders();
					
					continue;
				}
				$this->replyHeaders[] = $line;
			}
			else {
				if (isset($this->replyHeaders['transfer-encoding']) && $chunkedTransferRegex->match(end($this->replyHeaders['transfer-encoding']))) {
					// last chunk finished
					if ($chunkLength === 0) {
						// read hex data and trash chunk-extension
						list($hex) = explode(';', $line, 2);
						$chunkLength = hexdec($hex);
						
						// $chunkLength === 0 -> no more data
						if ($chunkLength === 0) {
							// clear remaining response
							while (!$remoteFile->gets(1024));
							
							// remove chunked from transfer-encoding
							$this->replyHeaders['transfer-encoding'] = array_filter(array_map(function ($element) use ($chunkedTransferRegex) {
								return $chunkedTransferRegex->replace($element, '');
							}, $this->replyHeaders['transfer-encoding']), 'trim');
							if (empty($this->replyHeaders['transfer-encoding'])) unset($this->replyHeaders['transfer-encoding']);
							
							// break out of main reading loop
							break;
						}
					}
					else {
						$this->replyBody .= $line;
						$chunkLength -= strlen($line);
						$bodyLength += strlen($line);
						if ($chunkLength === 0) $remoteFile->read(2); // CRLF
					}
				}
				else {
					$this->replyBody .= $line;
					$bodyLength += strlen($line);
				}
			}
		}
		
		if (isset($this->options['maxLength'])) $this->replyBody = substr($this->replyBody, 0, $this->options['maxLength']);
		
		$remoteFile->close();
		
		$this->parseReply();
	}
	
	/**
	 * Parses the reply headers.
	 */
	private function parseReplyHeaders() {
		$headers = array();
		$lastKey = '';
		foreach ($this->replyHeaders as $header) {
			if (strpos($header, ':') === false) {
				$headers[trim($header)] = array(trim($header));
				continue;
			}
			
			// 4.2 Header fields can be
			// extended over multiple lines by preceding each extra line with at
			// least one SP or HT.
			if (ltrim($header, "\t ") !== $header) {
				$headers[$lastKey][] = array_pop($headers[$lastKey]).' '.trim($header);
			}
			else {
				list($key, $value) = explode(':', $header, 2);
				
				$lastKey = $key;
				if (!isset($headers[$key])) $headers[$key] = array();
				$headers[$key][] = trim($value);
			}
		}
		// 4.2 Field names are case-insensitive.
		$this->replyHeaders = array_change_key_case($headers);
		if (isset($this->replyHeaders['transfer-encoding'])) $this->replyHeaders['transfer-encoding'] = array(implode(',', $this->replyHeaders['transfer-encoding']));
		$this->legacyHeaders = array_map('end', $headers);
		
		// get status code
		$statusLine = reset($this->replyHeaders);
		$regex = new Regex('^HTTP/1.\d+ +(\d{3})');
		if (!$regex->match($statusLine[0])) throw new SystemException("Unexpected status '".$statusLine."'");
		$matches = $regex->getMatches();
		$this->statusCode = $matches[1];
	}
	
	/**
	 * Parses the reply.
	 */
	private function parseReply() {
		// 4.4 Messages MUST NOT include both a Content-Length header field and a
		// non-identity transfer-coding. If the message does include a non-
		// identity transfer-coding, the Content-Length MUST be ignored.
		if (isset($this->replyHeaders['content-length']) && (!isset($this->replyHeaders['transfer-encoding']) || strtolower(end($this->replyHeaders['transfer-encoding'])) !== 'identity') && !isset($this->options['maxLength'])) {
			if (strlen($this->replyBody) != end($this->replyHeaders['content-length'])) {
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
				
				// 10.3.4 The response to the request can be found under a different URI and SHOULD
				// be retrieved using a GET method on that resource.
				if ($this->statusCode == '303') {
					$newRequest->options['method'] = 'GET';
					$newRequest->postParameters = array();
					$newRequest->addHeader('content-length', '');
					$newRequest->addHeader('content-type', '');
				}
				
				try {
					$newRequest->setURL(end($this->replyHeaders['location']));
				}
				catch (SystemException $e) {
					throw new SystemException("Received 'Location: ".end($this->replyHeaders['location'])."' from server, which is invalid.", 0, $e);
				}
				
				try {
					$newRequest->execute();
					
					// update data with data from the inner request
					$this->url = $newRequest->url;
					$this->statusCode = $newRequest->statusCode;
					$this->replyHeaders = $newRequest->replyHeaders;
					$this->legacyHeaders = $newRequest->legacyHeaders;
					$this->replyBody = $newRequest->replyBody;
				}
				catch (SystemException $e) {
					// update data with data from the inner request
					$this->url = $newRequest->url;
					$this->statusCode = $newRequest->statusCode;
					$this->replyHeaders = $newRequest->replyHeaders;
					$this->legacyHeaders = $newRequest->legacyHeaders;
					$this->replyBody = $newRequest->replyBody;
					
					throw $e;
				}
				
				return;
			break;
			
			case '206':
				// check, if partial content was expected
				if (!isset($this->headers['range'])) {
					throw new HTTPServerErrorException("Received unexpected status code '206' from server");
				}
				else if (!isset($this->replyHeaders['content-range'])) {
					throw new HTTPServerErrorException("Content-Range is missing in reply header");
				}
			break;
			
			case '401':
			case '402':
			case '403':
				throw new HTTPUnauthorizedException("Received status code '".$this->statusCode."' from server");
			break;
			
			case '404':
				throw new HTTPNotFoundException("Received status code '404' from server");
			break;
				
			default:
				// 6.1.1 However, applications MUST
				// understand the class of any status code, as indicated by the first
				// digit, and treat any unrecognized response as being equivalent to the
				// x00 status code of that class, with the exception that an
				// unrecognized response MUST NOT be cached.
				switch (substr($this->statusCode, 0, 1)) {
					case '2': // 200 and unknown 2XX
					case '3': // 300 and unknown 3XX
						// we are fine
					break;
					case '5': // 500 and unknown 5XX
						throw new HTTPServerErrorException("Received status code '".$this->statusCode."' from server");
					break;
					default:
						throw new SystemException("Received unhandled status code '".$this->statusCode."' from server");
					break;
				}
			break;
		}
	}
	
	/**
	 * Returns an array with the replied data.
	 * Note that the 'headers' element is deprecated and may be removed in the future.
	 * 
	 * @return	array
	 */
	public function getReply() {
		return array(
			'statusCode' => $this->statusCode, 
			'headers' => $this->legacyHeaders,
			'httpHeaders' => $this->replyHeaders,
			'body' => $this->replyBody,
			'url' => $this->url
		);
	}
	
	/**
	 * Sets options and applies default values when an option is omitted.
	 * 
	 * @param	array		$options
	 * @throws	SystemException
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
		// 4.2 Field names are case-insensitive.
		$name = strtolower($name);
		
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

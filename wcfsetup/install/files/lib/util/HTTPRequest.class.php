<?php
namespace wcf\util;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPServerErrorException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
use wcf\util\exception\HTTPException;

/**
 * Sends HTTP/1.1 requests.
 * It supports POST, SSL, Basic Auth etc.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 * @deprecated	5.3 - Use Guzzle via \wcf\system\io\HttpFactory.
 */
final class HTTPRequest {
	/**
	 * given options
	 * @var	array
	 */
	private $options = [];
	
	/**
	 * given post parameters
	 * @var	array
	 */
	private $postParameters = [];
	
	/**
	 * given files
	 * @var	array
	 */
	private $files = [];
	
	/**
	 * request URL
	 * @var	string
	 */
	private $url = '';
	
	/**
	 * request headers
	 * @var	string[][]
	 */
	private $headers = [];
	
	/**
	 * request body
	 * @var	string
	 */
	private $body = '';
	
	/**
	 * reply body
	 * @var	string
	 */
	private $replyBody;
	
	/**
	 * @var ResponseInterface
	 */
	private $response;
	
	/**
	 * Constructs a new instance of HTTPRequest.
	 * 
	 * @param	string		$url		URL to connect to
	 * @param	string[]	$options
	 * @param	mixed		$postParameters	Parameters to send via POST
	 * @param	array		$files		Files to attach to the request
	 */
	public function __construct($url, array $options = [], $postParameters = [], array $files = []) {
		$this->url = $url;
		
		$this->postParameters = $postParameters;
		$this->files = $files;
		
		$this->setOptions($options);
		
		// set default headers
		$language = WCF::getLanguage();
		$this->addHeader('user-agent', "HTTP.PHP (HTTPRequest.class.php; WoltLab Suite/".WCF_VERSION."; ".($language ? $language->languageCode : 'en').")");
		$this->addHeader('accept', '*/*');
		if ($language) $this->addHeader('accept-language', $language->getFixedLanguageCode());
		
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
				$boundary = bin2hex(\random_bytes(20));
				$this->addHeader('content-type', 'multipart/form-data; boundary='.$boundary);
				
				// source of the iterators: http://stackoverflow.com/a/7623716/782822
				if (!empty($this->postParameters)) {
					$iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->postParameters), \RecursiveIteratorIterator::SELF_FIRST);
					foreach ($iterator as $k => $v) {
						/** @noinspection PhpUndefinedMethodInspection */
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
					/** @noinspection PhpUndefinedMethodInspection */
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
		}
		$this->addHeader('connection', 'Close');
	}
	
	/**
	 * Executes the HTTP request.
	 */
	public function execute() {
		$redirectHandler = function(RequestInterface $request, ResponseInterface $response, UriInterface $uri) {
			$this->url = (string) $uri;
			$this->response = $response;
		};
		
		$options = [
			'timeout' => $this->options['timeout'],
			'allow_redirects' => [
				'max' => $this->options['maxDepth'],
				'track_redirects' => true,
				'on_redirect' => $redirectHandler,
			],
		];
		if (isset($this->options['auth'])) {
			$options['auth'] = [
				$this->options['auth']['username'],
				$this->options['auth']['password'],
			];
		}
		
		$client = HttpFactory::makeClient($options);
		
		$headers = [];
		foreach ($this->headers as $name => $values) {
			$headers[$name] = implode(', ', $values);
		}
		
		$request = new Request($this->options['method'], $this->url, $headers, $this->body);
		
		try {
			$this->response = $client->send($request, [
				// https://github.com/guzzle/guzzle/issues/2735
				'sink' => fopen("php://temp", "w+"),
			]);
		}
		catch (TooManyRedirectsException $e) {
			throw new HTTPException(
				$this,
				"Received status code '".$this->response->getStatusCode()."' from server, but recursion level is exhausted",
				$this->response->getStatusCode(),
				$e
			);
		}
		catch (BadResponseException $e) {
			$this->response = $e->getResponse();
			
			switch ($this->response->getStatusCode()) {
				case '401':
				case '402':
				case '403':
					throw new HTTPUnauthorizedException(
						"Received status code '".$this->response->getStatusCode()."' from server",
						0,
						'',
						new HTTPException(
							$this,
							"Received status code '".$this->response->getStatusCode()."' from server",
							(string) $this->response->getStatusCode(),
							$e
						)
					);
				case '404':
					throw new HTTPNotFoundException(
						"Received status code '404' from server",
						0,
						'',
						new HTTPException(
							$this,
							"Received status code '".$this->response->getStatusCode()."' from server",
							(string) $this->response->getStatusCode(),
							$e
						)
					);
				default:
					if (substr($this->response->getStatusCode(), 0, 1) == '5') {
						throw new HTTPServerErrorException(
							"Received status code '".$this->response->getStatusCode()."' from server",
							0,
							'',
							new HTTPException(
								$this,
								"Received status code '".$this->response->getStatusCode()."' from server",
								(string) $this->response->getStatusCode(),
								$e
							)
						);
					}
			}
		}
		catch (TransferException $e) {
			throw new SystemException('Failed to HTTPRequest', 0, '', $e);
		}
	}
	
	/**
	 * Returns an array with the replied data.
	 * Note that the 'headers' element is deprecated and may be removed in the future.
	 * 
	 * @return	array
	 */
	public function getReply() {
		$headers = [];
		$legacyHeaders = [];
		
		foreach ($this->response->getHeaders() as $name => $values) {
			$headers[strtolower($name)] = $values;
			$legacyHeaders[$name] = end($values);
		}
		
		if ($this->replyBody === null) {
			$bodyLength = 0;
			while (!$this->response->getBody()->eof()) {
				$toRead = 8192;
				if (isset($this->options['maxLength'])) {
					$toRead = min($toRead, $this->options['maxLength'] - $bodyLength);
				}
				
				$data = $this->response->getBody()->read($toRead);
				$this->replyBody .= $data;
				$bodyLength += strlen($data);
				
				if (isset($this->options['maxLength']) && $bodyLength >= $this->options['maxLength']) {
					$this->response->getBody()->close();
					break;
				}
			}
			if (isset($this->options['maxLength'])) {
				$this->replyBody = substr($this->replyBody, 0, $this->options['maxLength']);
			}
		}
		
		return [
			'statusCode' => (string) $this->response->getStatusCode(), 
			'headers' => $legacyHeaders,
			'httpHeaders' => $headers,
			'body' => $this->replyBody,
			'url' => $this->url,
		];
	}
	
	/**
	 * Sets options and applies default values when an option is omitted.
	 * 
	 * @param	array		$options
	 * @throws	\InvalidArgumentException
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
				throw new \InvalidArgumentException('Username is missing in authentication data.');
			}
			if (!isset($options['auth']['password'])) {
				throw new \InvalidArgumentException('Password is missing in authentication data.');
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
			$this->headers[$name] = [$value];
		}
	}
	
	/**
	 * Resets reply data when cloning.
	 */
	private function __clone() {
		$this->response = null;
	}
}

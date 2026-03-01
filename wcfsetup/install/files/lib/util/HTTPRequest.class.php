<?php

namespace wcf\util;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use ParagonIE\ConstantTime\Hex;
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
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  5.3 - Use Guzzle via \wcf\system\io\HttpFactory. Will be removed with 7.0.
 */
final class HTTPRequest
{
    /**
     * given options
     * @var mixed[]
     */
    private array $options = [];

    /**
     * given post parameters
     * @var mixed[]
     */
    private array $postParameters = [];

    /**
     * given files
     * @var mixed[]
     */
    private array $files = [];

    /**
     * request URL
     */
    private string $url = '';

    /**
     * request headers
     * @var string[][]
     */
    private array $headers = [];

    /**
     * request body
     */
    private string $body = '';

    /**
     * reply body
     */
    private ?string $replyBody = null;

    private ?ResponseInterface $response = null;

    /**
     * Constructs a new instance of HTTPRequest.
     *
     * @param mixed[] $options
     * @param mixed[] $postParameters Parameters to send via POST
     * @param mixed[] $files Files to attach to the request
     */
    public function __construct(string $url, array $options = [], array|string $postParameters = [], array $files = [])
    {
        $this->url = $url;

        $this->postParameters = $postParameters;
        $this->files = $files;

        $this->setOptions($options);

        // set default headers
        $language = WCF::getLanguage();
        $this->addHeader(
            'user-agent',
            "HTTP.PHP (HTTPRequest.class.php; WoltLab Suite/" . WCF_VERSION . "; " . $language->languageCode . ")"
        );
        $this->addHeader('accept', '*/*');
        $this->addHeader('accept-encoding', 'identity');
        $this->addHeader('accept-language', $language->getFixedLanguageCode());

        if (isset($this->options['maxLength'])) {
            $this->addHeader('Range', 'bytes=0-' . ($this->options['maxLength'] - 1));
        }

        if ($this->options['method'] !== 'GET') {
            if (empty($this->files)) {
                if (\is_array($postParameters)) {
                    $this->body = \http_build_query($this->postParameters, '', '&');
                } elseif (\is_string($postParameters) && !empty($postParameters)) { // @phpstan-ignore function.alreadyNarrowedType
                    $this->body = $postParameters;
                }

                $this->addHeader('content-type', 'application/x-www-form-urlencoded');
            } else {
                $boundary = Hex::encode(\random_bytes(20));
                $this->addHeader('content-type', 'multipart/form-data; boundary=' . $boundary);

                // source of the iterators: http://stackoverflow.com/a/7623716/782822
                if (!empty($this->postParameters)) {
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($this->postParameters),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach ($iterator as $v) {
                        if (!$iterator->hasChildren()) {
                            $key = '';
                            for ($i = 0, $max = $iterator->getDepth(); $i <= $max; $i++) {
                                if ($i === 0) {
                                    $key .= $iterator->getSubIterator($i)->key();
                                } else {
                                    $key .= '[' . $iterator->getSubIterator($i)->key() . ']';
                                }
                            }

                            $this->body .= "--" . $boundary . "\r\n";
                            $this->body .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n\r\n";
                            $this->body .= $v . "\r\n";
                        }
                    }
                }

                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveArrayIterator($this->files),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($iterator as $k => $v) {
                    if (!$iterator->hasChildren()) {
                        $key = '';
                        for ($i = 0, $max = $iterator->getDepth(); $i <= $max; $i++) {
                            if ($i === 0) {
                                $key .= $iterator->getSubIterator($i)->key();
                            } else {
                                $key .= '[' . $iterator->getSubIterator($i)->key() . ']';
                            }
                        }

                        $this->body .= "--" . $boundary . "\r\n";
                        $this->body .= 'Content-Disposition: form-data; name="' . $k . '"; filename="' . \basename($v) . '"' . "\r\n";
                        $this->body .= 'Content-Type: ' . (FileUtil::getMimeType($v) ?: 'application/octet-stream.') . "\r\n\r\n";
                        $this->body .= \file_get_contents($v) . "\r\n";
                    }
                }

                $this->body .= "--" . $boundary . "--";
            }
        }
        $this->addHeader('connection', 'Close');
    }

    /**
     * Executes the HTTP request.
     */
    public function execute(): void
    {
        $redirectHandler = function (RequestInterface $request, ResponseInterface $response, UriInterface $uri) {
            $this->url = (string)$uri;
            $this->response = $response;
        };

        $options = [
            // We cannot use the 'timeout' value as the overall timeout for compatibility with
            // pre-Guzzle users. However the combined connect and read timeout is not sufficient to
            // reliably terminate all requests timely. Thus we configure a large emergency timeout to
            // either the configured timeout or 10 minutes (whichever is larger). This ensures that the
            // request terminates eventually and the PHP worker will be freed.
            RequestOptions::TIMEOUT => \max($this->options['timeout'], 10 * 60),
            RequestOptions::CONNECT_TIMEOUT => $this->options['timeout'],
            RequestOptions::READ_TIMEOUT => $this->options['timeout'],
            RequestOptions::ALLOW_REDIRECTS => [
                'max' => $this->options['maxDepth'],
                'track_redirects' => true,
                'on_redirect' => $redirectHandler,
            ],
        ];
        if (isset($this->options['maxLength'])) {
            $options[RequestOptions::STREAM] = true;
        }
        if (isset($this->options['auth'])) {
            $options[RequestOptions::AUTH] = [
                $this->options['auth']['username'],
                $this->options['auth']['password'],
            ];
        }

        $client = HttpFactory::makeClient($options);

        $headers = [];
        foreach ($this->headers as $name => $values) {
            $headers[$name] = \implode(', ', $values);
        }

        $request = new Request($this->options['method'], $this->url, $headers, $this->body);

        try {
            $this->response = $client->send($request);
        } catch (TooManyRedirectsException $e) {
            throw new HTTPException(
                $this,
                "Received status code '" . $this->response->getStatusCode() . "' from server, but recursion level is exhausted",
                $this->response->getStatusCode(),
                $e
            );
        } catch (BadResponseException $e) {
            $this->response = $e->getResponse();

            switch ($this->response->getStatusCode()) {
                case '401':
                case '402':
                case '403':
                    throw new HTTPUnauthorizedException(
                        "Received status code '" . $this->response->getStatusCode() . "' from server",
                        0,
                        '',
                        new HTTPException(
                            $this,
                            "Received status code '" . $this->response->getStatusCode() . "' from server",
                            $this->response->getStatusCode(),
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
                            "Received status code '" . $this->response->getStatusCode() . "' from server",
                            $this->response->getStatusCode(),
                            $e
                        )
                    );
                default:
                    if (\str_starts_with((string)$this->response->getStatusCode(), '5')) {
                        throw new HTTPServerErrorException(
                            "Received status code '" . $this->response->getStatusCode() . "' from server",
                            0,
                            '',
                            new HTTPException(
                                $this,
                                "Received status code '" . $this->response->getStatusCode() . "' from server",
                                $this->response->getStatusCode(),
                                $e
                            )
                        );
                    }
            }
        } catch (TransferException $e) {
            throw new SystemException('Failed to HTTPRequest', 0, '', $e);
        }
    }

    /**
     * Returns an array with the replied data.
     * Note that the 'headers' element is deprecated and may be removed in the future.
     *
     * @return array{statusCode: int|string, headers: string[]|string[][], httpHeaders: string[]|string[][], body: string, url: string}
     */
    public function getReply(): array
    {
        if (!$this->response) {
            return [
                'statusCode' => 0,
                'headers' => [],
                'httpHeaders' => [],
                'body' => '',
                'url' => $this->url,
            ];
        }

        $headers = [];
        $legacyHeaders = [];

        foreach ($this->response->getHeaders() as $name => $values) {
            $headers[\strtolower($name)] = $values;
            $legacyHeaders[$name] = \end($values);
        }

        if ($this->replyBody === null) {
            try {
                $bodyLength = 0;
                while (!$this->response->getBody()->eof()) {
                    $toRead = 8192;
                    if (isset($this->options['maxLength'])) {
                        $toRead = \min($toRead, $this->options['maxLength'] - $bodyLength);
                    }

                    $data = $this->response->getBody()->read($toRead);
                    $this->replyBody .= $data;
                    $bodyLength += \strlen($data);

                    if (isset($this->options['maxLength']) && $bodyLength >= $this->options['maxLength']) {
                        break;
                    }
                }
            } finally {
                $this->response->getBody()->close();
            }

            if (isset($this->options['maxLength'])) {
                $this->replyBody = \substr($this->replyBody, 0, $this->options['maxLength']);
            }
        }

        return [
            'statusCode' => (string)$this->response->getStatusCode(),
            'headers' => $legacyHeaders,
            'httpHeaders' => $headers,
            'body' => $this->replyBody,
            'url' => $this->url,
        ];
    }

    /**
     * Sets options and applies default values when an option is omitted.
     *
     * @param mixed[] $options
     * @throws  \InvalidArgumentException
     */
    private function setOptions(array $options): void
    {
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
     */
    public function addHeader(string $name, string $value, bool $append = false): void
    {
        // 4.2 Field names are case-insensitive.
        $name = \strtolower($name);

        if ($value === '') {
            unset($this->headers[$name]);

            return;
        }

        if ($append && isset($this->headers[$name])) {
            $this->headers[$name][] = $value;
        } else {
            $this->headers[$name] = [$value];
        }
    }

    /**
     * Resets reply data when cloning.
     */
    private function __clone()
    {
        $this->response = null;
    }
}

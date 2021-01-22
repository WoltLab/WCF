<?php

namespace wcf\system\email\transport;

use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\transport\exception\PermanentFailure;
use wcf\system\email\transport\exception\TransientFailure;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * SmtpEmailTransport is an implementation of an email transport which sends emails via SMTP (RFC 5321, 3207 and 4954).
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Email\Transport
 * @since   3.0
 */
class SmtpEmailTransport implements IEmailTransport
{
    /**
     * SMTP connection
     * @var RemoteFile
     */
    protected $connection;

    /**
     * host of the smtp server to use
     * @var string
     */
    protected $host;

    /**
     * port to use
     * @var int
     */
    protected $port;

    /**
     * username to use for authentication
     * @var string
     */
    protected $username;

    /**
     * password corresponding to the username
     * @var string
     */
    protected $password;

    /**
     * STARTTLS encryption level
     * @var string
     */
    protected $starttls;

    /**
     * last value written to the server
     * @var string
     */
    protected $lastWrite = '';

    /**
     * ESMTP features advertised by the server
     * @var string[]
     */
    protected $features = [];

    /**
     * if this property is an instance of \Exception email delivery will be locked
     * and the \Exception will be thrown when attempting to deliver() an email
     * @var \Exception
     */
    protected $locked;

    /**
     * Creates a new SmtpEmailTransport using the given host.
     *
     * @param string $host host of the smtp server to use
     * @param int $port port to use
     * @param string $username username to use for authentication
     * @param string $password corresponding password
     * @param string $starttls one of 'none', 'may' and 'encrypt'
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        $host = MAIL_SMTP_HOST,
        $port = MAIL_SMTP_PORT,
        $username = MAIL_SMTP_USER,
        $password = MAIL_SMTP_PASSWORD,
        $starttls = MAIL_SMTP_STARTTLS
    ) {
        $this->host = StringUtil::trim($host);
        $this->port = \intval($port);
        $this->username = StringUtil::trim($username);
        $this->password = StringUtil::trim($password);

        switch ($starttls) {
            case 'none':
            case 'may':
            case 'encrypt':
                $this->starttls = $starttls;
                break;
            default:
                throw new \InvalidArgumentException(
                    "Invalid STARTTLS preference '" . $starttls . "'. Must be one of 'none', 'may' and 'encrypt'."
                );
        }
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Tests the connection by establishing a connection and optionally
     * providing user credentials. Returns the error message or an empty
     * string on success.
     *
     * @return      string
     */
    public function testConnection()
    {
        try {
            $this->connect(10);
            $this->auth();
        } catch (SystemException $e) {
            if (\strpos($e->getMessage(), 'Can not connect to') === 0) {
                return WCF::getLanguage()->get('wcf.acp.email.smtp.test.error.hostUnknown');
            }

            return $e->getMessage();
        } catch (PermanentFailure $e) {
            if (\strpos($e->getMessage(), 'Remote SMTP server does not support EHLO') === 0) {
                return WCF::getLanguage()->get('wcf.acp.email.smtp.test.error.notTlsSupport');
            } elseif (\strpos($e->getMessage(), 'Remote SMTP server does not advertise STARTTLS') === 0) {
                return WCF::getLanguage()->get('wcf.acp.email.smtp.test.error.notTlsSupport');
            } elseif (\strpos($e->getMessage(), "Remote SMTP server reported permanent error code: 535 (") === 0) {
                return WCF::getLanguage()->get('wcf.acp.email.smtp.test.error.badAuth');
            }

            return $e->getMessage();
        } catch (TransientFailure $e) {
            if (\strpos($e->getMessage(), 'Enabling TLS failed') === 0) {
                return WCF::getLanguage()->get('wcf.acp.email.smtp.test.error.tlsFailed');
            }

            return $e->getMessage();
        }

        $this->disconnect();

        return '';
    }

    /**
     * Reads a server reply and validates it against the given expected status codes.
     * Returns a tuple [ status code, reply text ].
     *
     * @param int[] $expectedCodes
     * @return  array
     * @throws  PermanentFailure
     * @throws  TransientFailure
     */
    protected function read(array $expectedCodes)
    {
        $truncateReply = static function ($reply) {
            return StringUtil::truncate(
                \preg_replace('/[\x00-\x1F\x80-\xFF]/', '.', $reply),
                80,
                StringUtil::HELLIP,
                true
            );
        };

        $code = null;
        $reply = '';
        do {
            $data = $this->connection->gets();
            if (\preg_match('/^(\d{3})([- ])(.*)$/', $data, $matches)) {
                if ($code === null) {
                    $code = \intval($matches[1]);

                    if (!\in_array($code, $expectedCodes)) {
                        // 4xx is a transient failure
                        if (400 <= $code && $code < 500) {
                            throw new TransientFailure("Remote SMTP server reported transient error code: " . $code . " (" . $truncateReply($matches[3]) . ") in reply to '" . $this->lastWrite . "'");
                        }

                        // 5xx is a permanent failure
                        if (500 <= $code && $code < 600) {
                            throw new PermanentFailure("Remote SMTP server reported permanent error code: " . $code . " (" . $truncateReply($matches[3]) . ") in reply to '" . $this->lastWrite . "'");
                        }

                        throw new TransientFailure("Remote SMTP server reported not expected code: " . $code . " (" . $truncateReply($matches[3]) . ") in reply to '" . $this->lastWrite . "'");
                    }
                }

                if ($code == $matches[1]) {
                    $reply .= \trim($matches[3]) . "\r\n";

                    // no more continuation lines
                    if ($matches[2] === ' ') {
                        break;
                    }
                } else {
                    throw new TransientFailure("Unexpected reply '" . $data . "' from SMTP server. Code does not match previous codes from multiline answer.");
                }
            } else {
                if ($this->connection->eof()) {
                    throw new TransientFailure("Unexpected EOF / connection close from SMTP server.");
                }

                throw new TransientFailure("Unexpected reply '" . $data . "' from SMTP server.");
            }
        } while (true);

        return [$code, $reply];
    }

    /**
     * Writes the given line to the server.
     *
     * @param string $data
     */
    protected function write($data)
    {
        $this->lastWrite = $data;
        $this->connection->write($data . "\r\n");
    }

    /**
     * Connects to the server and enables STARTTLS if available. Bails
     * out if STARTTLS is not available and connection is set to 'encrypt'.
     *
     * @param int $overrideTimeout
     * @throws  PermanentFailure
     */
    protected function connect($overrideTimeout = null)
    {
        if ($overrideTimeout === null) {
            $this->connection = new RemoteFile($this->host, $this->port);
        } else {
            $this->connection = new RemoteFile($this->host, $this->port, $overrideTimeout);
        }

        $this->read([220]);

        try {
            $this->write('EHLO ' . Email::getHost());
            $this->features = \array_map(
                'strtolower',
                \explode("\n", StringUtil::unifyNewlines($this->read([250])[1]))
            );
        } catch (SystemException $e) {
            if ($this->starttls == 'encrypt') {
                throw new PermanentFailure(
                    "Remote SMTP server does not support EHLO, but \$starttls is set to 'encrypt'."
                );
            }

            $this->write('HELO ' . Email::getHost());
            $this->features = [];
        }

        switch ($this->starttls) {
            case 'encrypt':
                if (!\in_array('starttls', $this->features)) {
                    throw new PermanentFailure(
                        "Remote SMTP server does not advertise STARTTLS, but \$starttls is set to 'encrypt'."
                    );
                }

                $this->starttls();

                $this->write('EHLO ' . Email::getHost());
                $this->features = \array_map(
                    'strtolower',
                    \explode("\n", StringUtil::unifyNewlines($this->read([250])[1]))
                );
                break;
            case 'may':
                if (\in_array('starttls', $this->features)) {
                    try {
                        $this->starttls();

                        $this->write('EHLO ' . Email::getHost());
                        $this->features = \array_map(
                            'strtolower',
                            \explode("\n", StringUtil::unifyNewlines($this->read([250])[1]))
                        );
                    } catch (\Exception $e) {
                        \wcf\functions\exception\logThrowable($e);
                        $this->disconnect();
                        $this->starttls = 'none';
                        $this->connect();
                    }
                }
                break;
            case 'none':
                // nothing to do here
        }
    }

    /**
     * Enables STARTTLS on the connection.
     *
     * @throws  TransientFailure
     */
    protected function starttls()
    {
        $this->write("STARTTLS");
        $this->read([220]);

        try {
            if (!$this->connection->setTLS(true)) {
                throw new TransientFailure('Enabling TLS failed');
            }
        } catch (SystemException $e) {
            throw new TransientFailure('Enabling TLS failed', 0, $e);
        }
    }

    /**
     * Performs SASL authentication using the credentials provided in the
     * constructor. Supported mechanisms are LOGIN and PLAIN.
     */
    protected function auth()
    {
        if (!$this->username || !$this->password) {
            return;
        }

        $authException = null;
        foreach ($this->features as $feature) {
            $parameters = \explode(" ", $feature);

            if ($parameters[0] == 'auth') {
                // Try mechanisms in order of preference.
                foreach (['login', 'plain'] as $method) {
                    if (\in_array($method, $parameters)) {
                        switch ($method) {
                            case 'login':
                                try {
                                    $this->write('AUTH LOGIN');
                                    $this->read([334]);
                                } catch (SystemException $e) {
                                    $authException = $e;
                                    // try next authentication method
                                    continue 2;
                                }
                                $this->write(\base64_encode($this->username));
                                $this->lastWrite = '*redacted*';
                                $this->read([334]);
                                $this->write(\base64_encode($this->password));
                                $this->lastWrite = '*redacted*';
                                $this->read([235]);

                                // Authentication was successful.
                                return;
                                break;
                            case 'plain':
                                // RFC 4616
                                try {
                                    $this->write('AUTH PLAIN');
                                    $this->read([334]);
                                } catch (SystemException $e) {
                                    $authException = $e;
                                    // try next authentication method
                                    continue 2;
                                }
                                $this->write(\base64_encode("\0" . $this->username . "\0" . $this->password));
                                $this->lastWrite = '*redacted*';
                                $this->read([235]);

                                // Authentication was successful.
                                return;
                        }
                    }
                }

                // No mechanism was accepted.
                break;
            }
        }

        // server does not support auth
        throw new TransientFailure(
            "Remote SMTP server does not support AUTH, but SMTP credentials are specified.",
            0,
            $authException
        );
    }

    /**
     * Cleanly closes the connection to the server.
     */
    protected function disconnect()
    {
        if ($this->connection) {
            try {
                $this->write("QUIT");
                $this->connection->close();
            } catch (SystemException $e) {
                // quit failed, don't care about it
            } finally {
                $this->connection = null;
            }
        }
    }

    /**
     * Delivers the given email using SMTP.
     *
     * @param Email $email
     * @param Mailbox $envelopeFrom
     * @param Mailbox $envelopeTo
     * @throws  \Exception
     * @throws  PermanentFailure
     */
    public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo)
    {
        // delivery is locked
        if ($this->locked instanceof \Exception) {
            throw $this->locked;
        }

        if (!$this->connection || $this->connection->eof()) {
            try {
                $this->connect();
                $this->auth();
            } catch (PermanentFailure $e) {
                // lock delivery on permanent failure to avoid spamming the SMTP server
                $this->locked = $e;
                $this->disconnect();
                throw $e;
            } catch (\Exception $e) {
                $this->disconnect();
                throw $e;
            }
        }

        $this->write('RSET');
        $this->read([250]);
        $this->write('MAIL FROM:<' . $envelopeFrom->getAddress() . '>');
        $this->read([250]);
        $this->write('RCPT TO:<' . $envelopeTo->getAddress() . '>');
        $this->read([250, 251]);
        $this->write('DATA');
        $this->read([354]);
        $this->connection->write(\implode("\r\n", \array_map(static function ($item) {
            // 4.5.2 Transparency
            // o  Before sending a line of mail text, the SMTP client checks the
            //    first character of the line.  If it is a period, one additional
            //    period is inserted at the beginning of the line.
            if (StringUtil::startsWith($item, '.')) {
                return '.' . $item;
            }

            return $item;
        }, \explode("\r\n", $email->getEmail()))) . "\r\n");
        $this->write(".");
        $this->read([250]);
    }
}

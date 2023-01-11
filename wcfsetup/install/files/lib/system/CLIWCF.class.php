<?php

namespace wcf\system;

use phpline\console\ConsoleReader;
use phpline\console\history\MemoryHistory;
use phpline\internal\Log;
use phpline\TerminalFactory;
use wcf\data\session\SessionEditor;
use wcf\system\cli\command\CLICommandHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\authentication\UserAuthenticationFactory;
use wcf\util\FileUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\Loader\StandardAutoloader as ZendLoader;

// phpcs:disable PSR1.Files.SideEffects

// set exception handler
\set_exception_handler([CLIWCF::class, 'handleCLIException']);

/**
 * Extends WCF class with functions for CLI.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System
 */
class CLIWCF extends WCF
{
    /**
     * instance of ConsoleReader
     * @var ConsoleReader
     */
    protected static $consoleReader;

    /**
     * instance of ArgvParser
     * @var \Zend\Console\Getopt
     */
    protected static $argvParser;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Calls all init functions of the WCF class.
     */
    public function __construct()
    {
        // define tmp directory
        if (!\defined('TMP_DIR')) {
            \define('TMP_DIR', FileUtil::getTempFolder());
        }

        // register additional autoloaders
        require_once(WCF_DIR . 'lib/system/api/phpline/phpline.phar');
        require_once(WCF_DIR . 'lib/system/api/zend/Loader/StandardAutoloader.php');
        $zendLoader = new ZendLoader([ZendLoader::AUTOREGISTER_ZF => true]);
        $zendLoader->register();

        // disable benchmark
        \define('ENABLE_BENCHMARK', 0);

        // start initialization
        $this->initDB();
        $this->loadOptions();
        $this->initSession();
        $this->initLanguage();
        $this->initTPL();
        $this->initCoreObjects();
        $this->initApplications();

        $this->runBootstrappers();

        // the destructor registered in core.functions.php will only call the destructor of the parent class
        \register_shutdown_function([self::class, 'destruct']);

        $this->initArgv();
        $this->initPHPLine();
        $this->initAuth();
        $this->initCommands();
    }

    /**
     * @inheritDoc
     */
    public static function destruct()
    {
        if (empty($_ENV['WCF_SESSION_ID'])) {
            self::getSession()->delete();
        }
    }

    /**
     * @inheritDoc
     */
    final public static function handleCLIException($e)
    {
        exit($e->getMessage() . "\n" . $e->getTraceAsString());
    }

    /**
     * Initializes parsing of command line options.
     */
    protected function initArgv()
    {
        // initialise ArgvParser
        self::$argvParser = new ArgvParser([
            'v' => WCF::getLanguage()->get('wcf.cli.help.v'),
            'q' => WCF::getLanguage()->get('wcf.cli.help.q'),
            'h|help' => WCF::getLanguage()->get('wcf.cli.help.help'),
            'version' => WCF::getLanguage()->get('wcf.cli.help.version'),
            'exitOnFail' => WCF::getLanguage()->get('wcf.cli.help.exitOnFail'),
        ]);
        self::getArgvParser()->setOptions([
            ArgvParser::CONFIG_CUMULATIVE_FLAGS => true,
            ArgvParser::CONFIG_DASHDASH => false,
        ]);

        try {
            self::getArgvParser()->parse();
        } catch (ArgvException $e) {
            // show error message and usage
            echo $e->getMessage() . \PHP_EOL;
            echo self::getArgvParser()->getUsageMessage();

            exit;
        }

        // handle arguments
        if (self::getArgvParser()->help) {
            // show usage
            echo self::getArgvParser()->getUsageMessage();

            exit;
        }
        if (self::getArgvParser()->version) {
            // show version
            echo WCF_VERSION . \PHP_EOL;

            exit;
        }
        if (\in_array('moo', self::getArgvParser()->getRemainingArgs())) {
            echo '...."Have you mooed today?"...' . \PHP_EOL;
        }

        \define('VERBOSITY', self::getArgvParser()->v - self::getArgvParser()->q);
    }

    /**
     * Returns the argv parser.
     *
     * @return  \Zend\Console\Getopt
     */
    public static function getArgvParser()
    {
        return self::$argvParser;
    }

    /**
     * Initializes PHPLine.
     */
    protected function initPHPLine()
    {
        $terminal = TerminalFactory::get();
        self::$consoleReader = new ConsoleReader("WoltLab Suite", null, null, $terminal);

        // don't expand events, as the username and password will follow
        self::getReader()->setExpandEvents(false);

        if (VERBOSITY >= 0) {
            $headline = \str_pad(
                "WoltLab Suite (tm) " . WCF_VERSION,
                self::getTerminal()->getWidth(),
                " ",
                \STR_PAD_BOTH
            );
            self::getReader()->println($headline);
        }
    }

    /**
     * Returns ConsoleReader.
     *
     * @return  ConsoleReader
     */
    public static function getReader()
    {
        return self::$consoleReader;
    }

    /**
     * Returns the terminal that is attached to ConsoleReader
     *
     * @return  \phpline\Terminal
     */
    public static function getTerminal()
    {
        return self::getReader()->getTerminal();
    }

    /**
     * Does the user authentification.
     */
    protected function initAuth()
    {
        self::getReader()->setHistoryEnabled(false);
        if (!empty($_ENV['WCF_SESSION_ID'])) {
            self::getSession()->delete();
            self::getSession()->load(SessionEditor::class, $_ENV['WCF_SESSION_ID']);
            if (!self::getUser()->userID) {
                self::getReader()->println('Invalid sessionID');

                exit(1);
            }
        } else {
            do {
                $line = self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.username') . '> ');
                if ($line === null) {
                    exit;
                }
                $username = StringUtil::trim($line);
            } while ($username === '');

            do {
                $line = self::getReader()->readLine(WCF::getLanguage()->get('wcf.user.password') . '> ', '*');
                if ($line === null) {
                    exit;
                }
                $password = StringUtil::trim($line);
            } while ($password === '');

            // check credentials and switch user
            try {
                $user = UserAuthenticationFactory::getInstance()->getUserAuthentication()->loginManually(
                    $username,
                    $password
                );
                WCF::getSession()->changeUser($user);
            } catch (UserInputException $e) {
                $message = WCF::getLanguage()->getDynamicVariable(
                    'wcf.user.' . $e->getField() . '.error.' . $e->getType(),
                    ['username' => $username]
                );
                self::getReader()->println($message);

                exit(1);
            }
        }
        self::getReader()->setHistoryEnabled(true);
        self::getReader()->setHistory(new MemoryHistory());

        // initialize language
        if (!self::getArgvParser()->language) {
            $this->initLanguage();
        }
    }

    /**
     * Initializes command handling.
     */
    protected function initCommands()
    {
        // Workaround to load commands for CLICommandHandler::getCommand().
        CLICommandHandler::getCommands();

        while (true) {
            // roll back open transactions of the previous command, as they are dangerous in a long living script
            if (WCF::getDB()->rollBackTransaction()) {
                Log::warn('Previous command had an open transaction.');
            }
            self::getReader()->setHistoryEnabled(true);
            $line = self::getReader()->readLine('>');
            if ($line === null) {
                exit;
            }
            $line = StringUtil::trim($line);
            try {
                $command = CLICommandHandler::getCommand($line);
                $command->execute(CLICommandHandler::getParameters($line));
            } catch (IllegalLinkException $e) {
                Log::error('notFound:' . JSON::encode(['command' => $line]));
                self::getReader()->println(WCF::getLanguage()->getDynamicVariable(
                    'wcf.cli.error.command.notFound',
                    ['command' => $line]
                ));

                if (self::getArgvParser()->exitOnFail) {
                    exit(1);
                }
                continue;
            } catch (PermissionDeniedException $e) {
                Log::error('permissionDenied');
                self::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.page.error.permissionDenied'));

                if (self::getArgvParser()->exitOnFail) {
                    exit(1);
                }
                continue;
            } catch (ArgvException $e) {
                // show error message and usage
                if ($e->getMessage()) {
                    echo $e->getMessage() . \PHP_EOL;
                }
                echo $e->getUsageMessage();

                if (self::getArgvParser()->exitOnFail) {
                    exit(1);
                }
                continue;
            } catch (\Exception $e) {
                Log::error($e);

                if (self::getArgvParser()->exitOnFail) {
                    exit(1);
                }
                continue;
            }
        }
    }
}

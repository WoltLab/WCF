<?php

namespace wcf\system\cli\command;

use phpline\internal\Log;
use wcf\system\CLIWCF;
use wcf\system\io\File;
use wcf\system\Regex;
use wcf\system\worker\IWorker;
use wcf\util\CLIUtil;
use wcf\util\DirectoryUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;
use Zend\Console\Exception\RuntimeException as ArgvException;
use Zend\Console\Getopt as ArgvParser;
use Zend\ProgressBar\Adapter\Console as ConsoleProgressBar;
use Zend\ProgressBar\ProgressBar;

/**
 * Executes cronjobs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cli\Command
 */
class WorkerCLICommand implements IArgumentedCLICommand
{
    /**
     * arguments parser
     * @var \Zend\Console\Getopt
     */
    protected $argv;

    /**
     * Initializes the argument parser.
     */
    public function __construct()
    {
        $this->argv = new ArgvParser([
            'l|list' => CLIWCF::getLanguage()->get('wcf.cli.worker.list'),
            'setParameter=s' => CLIWCF::getLanguage()->get('wcf.cli.worker.setParameter'),
            'threads=i' => CLIWCF::getLanguage()->get('wcf.cli.worker.threads'),
            'threadId=i' => CLIWCF::getLanguage()->get('wcf.cli.worker.threadId'),
        ]);
        $this->argv->setOptions([
            ArgvParser::CONFIG_FREEFORM_FLAGS => true,
            ArgvParser::CONFIG_PARSEALL => false,
            ArgvParser::CONFIG_CUMULATIVE_PARAMETERS => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function execute(array $parameters)
    {
        $this->argv->setArguments($parameters);
        $this->argv->parse();

        if ($this->argv->list) {
            CLIWCF::getReader()->println(CLIUtil::generateTable($this->generateList()));

            return;
        }

        $threads = 1;
        $threadId = null;
        if ($this->argv->threads !== null) {
            $threads = $this->argv->threads;
            if ($this->argv->threadId !== null) {
                $threadId = $this->argv->threadId;
                if ($this->argv->threadId >= $this->argv->threads) {
                    CLIWCF::getReader()->println(CLIWCF::getLanguage()->get('wcf.cli.worker.threadId.invalid'));

                    return;
                }
            }
        }

        $args = $this->argv->getRemainingArgs();
        // validate parameters
        if (\count($args) != 1) {
            throw new ArgvException('', $this->getUsage());
        }

        $class = $args[0];

        // assume wcf\system\worker when no FQN is given
        if (\strpos($class, '\\') === false) {
            $class = 'wcf\system\worker\\' . $class;
        }

        $invalid = false;
        if (!\class_exists($class)) {
            $invalid = true;
        } else {
            $reflection = new \ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                $invalid = true;
            } elseif (!\is_subclass_of($class, IWorker::class)) {
                $invalid = true;
            }
        }
        if ($invalid) {
            throw new ArgvException("Invalid worker '" . $class . "' given", $this->getUsage());
        }

        // parse parameters
        $options = $this->argv->getOptions();
        $parameters = [];
        foreach ($options as $option) {
            $value = $this->argv->getOption($option);
            if ($option === 'setParameter') {
                if (!\is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $parameter) {
                    [$parameterKey, $parameterValue] = \explode('=', $parameter);
                    $parameters[$parameterKey] = $parameterValue;
                }
            } else {
                $parameters[$option] = $value;
            }
        }

        /** @var IWorker $worker */
        $worker = new $class($parameters);
        $worker->validate();
        $worker->getProgress(); // make sure objects are counted

        // If multithreading is requested, but no threadId is given then this
        // is the controller process invoked by the Administrator.
        if ($threads > 1 && $threadId === null) {
            if (\PHP_EOL === "\r\n") {
                CLIWCF::getReader()->println(CLIWCF::getLanguage()->get('wcf.cli.worker.threads.windows'));

                return;
            }
            $this->spawnController($worker, $threads);

            return;
        }

        $progressbar = new ProgressBar(new ConsoleProgressBar([
            'width' => CLIWCF::getTerminal()->getWidth(),
        ]));
        $progress = 0;
        $output = null;
        if (!empty($_ENV['WCF_WORKER_STATUS_FD'])) {
            $output = new File("php://fd/" . $_ENV['WCF_WORKER_STATUS_FD'], "w");
        }
        try {
            for ($i = ($threadId !== null ? ($threadId + 1) : 0); $progress < 100; $i += $threads) {
                $worker->setLoopCount($i);
                $worker->validate();

                // execute worker
                $worker->execute();
                $worker->finalize();

                // update progress
                $progress = $worker->getProgress();
                $progressbar->update($progress);
                if ($output) {
                    $output->write(JSON::encode([
                            'iteration' => $i,
                            'progress' => $progress,
                        ]) . "\n");
                }
            }
        } catch (\Exception $e) {
            if ($output) {
                $output->write(JSON::encode([
                    'error' => (string)$e,
                ]));
            }
            throw $e;
        }
        if ($output) {
            $output->write(JSON::encode([
                    'finished' => true,
                    'progress' => $progress,
                ]) . "\n");
            $output->close();
        }
        $progressbar->update($progress);

        $progressbar->finish();
    }

    /**
     * Contains the logic of the parent controller process in multithreading mode.
     */
    protected function spawnController(IWorker $worker, $threads)
    {
        // Perform the first iteration before spawning the worker processes.
        // The first iteration is usually used to perform clean-up operations
        // that might interfere with the rebuilding happening within the workers.
        $worker->setLoopCount(0);
        $worker->validate();
        $worker->execute();
        $worker->finalize();

        $fileDescriptors = [
            // Bind STDIN to a pipe to allow sending the command.
            0 => ["pipe", "r"],
            // Close STDOUT / STDERR.
            1 => ["file", "/dev/null", "a"],
            2 => ["file", "/dev/null", "a"],
            // Open a pipe as a communication channel for status updates.
            3 => ["pipe", "w"],
        ];

        // Invoke the worker processes with the same command line ...
        $arguments = $_SERVER['argv'];
        // ... with the quiet argument ...
        $arguments[] = '-qqqqq';
        // ... and requesting an unclean exit of an Exception is thrown.
        $arguments[] = '--exitOnFail';
        $commandLine = \PHP_BINARY . ' ' . \implode(' ', \array_map('escapeshellarg', $arguments));

        Log::debug('Using "' . $commandLine . '" as the worker command line.');

        // Reuse the environment.
        // - Specify TERM=dumb to prevent the worker from messing around with our terminal.
        // - Specify WCF_WORKER_STATUS_FD to prevent the administrator from manually giving a threadId,
        //   causing the worker to write into an arbitrary file descriptor (most likely the DB connection).
        // - Specify WCF_SESSION_ID to perform the login.
        $env = \array_merge($_ENV, [
            'TERM' => 'dumb',
            'WCF_WORKER_STATUS_FD' => 3,
            'WCF_SESSION_ID' => CLIWCF::getSession()->sessionID,
        ]);

        try {
            // 1) Spawn the processes.
            $processes = [];
            for ($threadId = 0; $threadId < $threads; $threadId++) {
                $process = \proc_open($commandLine, $fileDescriptors, $pipes, null, $env, null);

                $consoleBar = new ConsoleProgressBar([
                    'width' => CLIWCF::getTerminal()->getWidth(),
                ]);
                $consoleBar->setElements([
                    ConsoleProgressBar::ELEMENT_PERCENT,
                    ConsoleProgressBar::ELEMENT_BAR,
                    ConsoleProgressBar::ELEMENT_ETA,
                    ConsoleProgressBar::ELEMENT_TEXT,
                ]);
                $consoleBar->setTextWidth(30);
                $progressbar = new ProgressBar($consoleBar);
                $progressbar->update(0, 'T' . $threadId . ': spawned');
                echo "\n";

                $processes[] = [
                    'threadId' => $threadId,
                    'pipes' => $pipes,
                    'process' => $process,
                    'progressbar' => $progressbar,
                ];
            }

            // 2) Start processing in all processes.
            foreach ($processes as $processData) {
                $workerCommand = 'worker --threads=' . $threads . ' --threadId=' . $processData['threadId'] . ' "' . \addcslashes(\get_class($worker),
                        "\\\"");
                \fwrite($processData['pipes'][0], $workerCommand . "\n");
                \fclose($processData['pipes'][0]);
            }

            // 3) Handle their status output.
            while (true) {
                // 3.1) Check which processes' status FD is readable.
                $read = \array_filter(\array_map(static function ($processData) {
                    return $processData['pipes'][3];
                }, $processes), static function ($handle) {
                    return $handle !== false;
                });
                $write = null;
                $except = null;

                // 3.2) Exit if all status FDs have been closed.
                if (empty($read)) {
                    break;
                }

                \stream_select($read, $write, $except, 2, null);

                // 3.3) Rerender the progressbars with the updated status information.
                // Move up $threads lines to move into the line of the first progress bar.
                echo "\033[" . $threads . "A";
                $cursorOffset = -$threads;
                foreach ($processes as &$processData) {
                    $status = \proc_get_status($processData['process']);
                    if ($status === false) {
                        throw new \LogicException('Unreachable');
                    }

                    $line = false;
                    if ($processData['pipes'][3] !== false) {
                        if (\in_array($processData['pipes'][3], $read)) {
                            $line = \fgets($processData['pipes'][3]);
                        }

                        if (\feof($processData['pipes'][3])) {
                            \fclose($processData['pipes'][3]);
                            $processData['pipes'][3] = false;
                        }
                    }

                    $statusPrefix = 'T' . $processData['threadId'] . ': ';
                    if ($line) {
                        // If a line could be read we update the progressbar with the data sent.
                        $parsedLine = JSON::decode(StringUtil::trim($line));

                        if (!empty($parsedLine['error'])) {
                            \fwrite(\STDERR, \str_repeat("\n", -$cursorOffset + 1));
                            \fwrite(\STDERR, \str_repeat("=", 20) . "\n");
                            \fwrite(\STDERR, "Error in thread " . $processData['threadId'] . "\n");
                            \fwrite(\STDERR, $parsedLine['error'] . "\n");
                            \fwrite(\STDERR, \str_repeat("=", 20) . "\n");
                            \fwrite(\STDERR, \str_repeat("\n", $threads + $cursorOffset + 1));
                        } elseif (isset($parsedLine['finished'])) {
                            $processData['progressbar']->update(
                                $parsedLine['progress'],
                                $statusPrefix . 'finished'
                            );
                        } else {
                            $processData['progressbar']->update(
                                $parsedLine['progress'],
                                $statusPrefix . 'loop#' . $parsedLine['iteration']
                            );
                        }
                    } elseif (!$status['running']) {
                        // If the process exited we update the text status to indicate so.
                        $processData['progressbar']->update(null, $statusPrefix . 'exited');
                    } else {
                        // Otherwise just rerender the bar without changing anything.
                        $processData['progressbar']->update();
                    }
                    echo "\n";
                    $cursorOffset++;

                    // Check the exit code after processing the status line, to allow for dumping the error message.
                    if (!$status['running'] && $status['exitcode'] != -1) {
                        if ($status['exitcode']) {
                            throw new \Exception(
                                'Unclean exit of thread ' . $processData['threadId'] . ' detected. Exiting.'
                            );
                        }
                    }
                }
                unset($processData);
            }

            // 4) Make sure to update the text status to 'exited' for every process to not confuse the user.
            echo "\033[" . $threads . "A";
            foreach ($processes as $processData) {
                $status = \proc_get_status($processData['process']);
                if ($status === false) {
                    throw new \LogicException('Unreachable');
                }

                if (!$status['running']) {
                    $processData['progressbar']->update(null, 'T' . $processData['threadId'] . ': exited');
                } else {
                    throw new \LogicException('Unreachable');
                }
                echo "\n";
            }
        } finally {
            if ($cursorOffset) {
                // Move out of the progress bar area.
                echo \str_repeat("\n", -$cursorOffset);
            }
            echo "\n";

            // Kill all remaining processes and check their status to not leave zombies.
            // There should not be any running processes at this point, except in case of
            // an unclean exit of one process
            foreach ($processes as $processData) {
                if (\proc_get_status($processData['process'])['running']) {
                    echo "Killing thread " . $processData['threadId'] . "\n";
                    \proc_terminate($processData['process'], 9);
                    $i = 0;
                    while (\proc_get_status($processData['process'])['running']) {
                        \usleep(100000);
                        if ($i++ > 3) {
                            echo "Waiting for exit\n";
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns an array with the list of all available workers.
     *
     * @return  array
     */
    public function generateList()
    {
        $directory = DirectoryUtil::getInstance(WCF_DIR . 'lib/system/worker/');
        $workerList = $directory->getFiles(\SORT_ASC, new Regex('Worker\.class\.php$'));

        $table = [
            [
                'Class',
                'Description',
            ],
        ];
        foreach ($workerList as $worker) {
            $class = 'wcf\system\worker\\' . \basename($worker, '.class.php');
            if (!\class_exists($class) && !\interface_exists($class)) {
                Log::info('Invalid worker file: ', $worker);
                continue;
            }
            $reflection = new \ReflectionClass($class);
            if (!$reflection->isInstantiable()) {
                continue;
            }

            if (!\is_subclass_of($class, IWorker::class)) {
                Log::info('Invalid worker file: ', $worker);
                continue;
            }

            $docComment = \explode("\n", StringUtil::unifyNewlines($reflection->getDocComment()));
            $comment = '';
            foreach ($docComment as $commentLine) {
                if (Regex::compile('[a-z]', Regex::CASE_INSENSITIVE)->match($commentLine)) {
                    $comment = Regex::compile('^[^a-z]+', Regex::CASE_INSENSITIVE)->replace($commentLine, '');
                    break;
                }
            }

            $table[] = [
                \basename($worker, '.class.php'),
                $comment,
            ];
        }

        return $table;
    }

    /**
     * @inheritDoc
     */
    public function getUsage()
    {
        return \str_replace(
            $_SERVER['argv'][0] . ' [ options ]',
            'worker [ options ] <worker>',
            $this->argv->getUsageMessage()
        );
    }

    /**
     * @inheritDoc
     */
    public function canAccess()
    {
        return true;
    }
}

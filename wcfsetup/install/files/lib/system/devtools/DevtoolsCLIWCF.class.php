<?php

namespace wcf\system\devtools;

use wcf\command\devtools\project\SyncPackageInstallationPlugin;
use wcf\command\devtools\project\SyncPackageVersion;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\devtools\project\DevtoolsProjectList;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\system\CLIWCF;
use wcf\system\devtools\pip\DevtoolsPip;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Non-interactive command line interface of the developer tools, used by
 * `devtools.php` to sync projects without the ACP.
 *
 * Every sync step runs in a child process (`invoke`), matching the one request
 * per step of the ACP, so that no step sees runtime state left behind by the
 * previous one. It extends `CLIWCF` without the interactive console to run in
 * the same environment as the CLI: event listeners of the `admin` environment
 * and a regular guest session instead of an on-demand one.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DevtoolsCLIWCF extends CLIWCF
{
    private const EXIT_SUCCESS = 0;

    private const EXIT_FAILURE = 1;

    private const EXIT_USAGE = 2;

    /**
     * Returned by `invoke` if the PIP has split its work and must be invoked
     * again for the same target.
     */
    private const EXIT_INVOKE_AGAIN = 3;

    /**
     * Options accepted by each command, `invoke` is internal and therefore
     * omitted from the usage.
     */
    private const COMMANDS = [
        'help' => [],
        'projects' => [],
        'targets' => ['project', 'pip'],
        'sync' => ['project', 'pip', 'target', 'all', 'allow-unsafe'],
        'invoke' => ['project', 'pip', 'target', 'allow-unsafe'],
    ];

    private const REQUIRED_OPTIONS = [
        'help' => [],
        'projects' => [],
        'targets' => ['project'],
        'sync' => ['project'],
        'invoke' => ['project', 'pip', 'target'],
    ];

    private const FLAGS = ['all', 'allow-unsafe'];

    /**
     * @phpstan-ignore constructor.missingParentCall
     */
    public function __construct()
    {
        \set_exception_handler(self::handleUncaughtException(...));

        if (!\defined('TMP_DIR')) {
            \define('TMP_DIR', FileUtil::getTempFolder());
        }

        \define('ENABLE_BENCHMARK', 0);

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
    }

    /**
     * Runs the command and returns the exit code.
     *
     * @param list<string> $arguments
     */
    public function run(array $arguments): int
    {
        $command = \array_shift($arguments) ?? 'help';
        if (!isset(self::COMMANDS[$command])) {
            $this->error("Unknown command '{$command}'.");
            $this->printUsageHint();

            return self::EXIT_USAGE;
        }

        $options = $this->parseOptions($command, $arguments);
        if ($options === null || !$this->validateOptions($command, $options)) {
            $this->printUsageHint();

            return self::EXIT_USAGE;
        }

        if ($command === 'help') {
            $this->printUsage();

            return self::EXIT_SUCCESS;
        }

        if (\ENABLE_DEVELOPER_TOOLS === 0) {
            $this->error('The developer tools are disabled.');

            return self::EXIT_FAILURE;
        }

        return match ($command) {
            'projects' => $this->listProjects(),
            'targets' => $this->listTargets($options),
            'sync' => $this->sync($options),
            'invoke' => $this->invoke($options),
        };
    }

    private function listProjects(): int
    {
        $projectList = new DevtoolsProjectList();
        $projectList->sqlOrderBy = 'name';
        $projectList->readObjects();

        foreach ($projectList as $project) {
            $this->write("{$project->projectID}\t{$project->name}\t{$project->path}");
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * @param array<string, string|true> $options
     */
    private function listTargets(array $options): int
    {
        $project = $this->getProject($options['project']);
        if ($project === null) {
            return self::EXIT_FAILURE;
        }

        if (isset($options['pip'])) {
            $pip = $this->getPip($options['pip']);
            if ($pip === null) {
                return self::EXIT_FAILURE;
            }

            $pips = [$pip];
        } else {
            $pips = $project->getPips();
        }

        foreach ($pips as $pip) {
            foreach ($pip->getTargets($project) as $target) {
                $this->write("{$pip->pluginName}\t{$target}" . ($pip->isUnsafe() ? "\tunsafe" : ''));
            }
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * @param array<string, string|true> $options
     */
    private function sync(array $options): int
    {
        $project = $this->getProject($options['project']);
        if ($project === null) {
            return self::EXIT_FAILURE;
        }

        if (isset($options['all'])) {
            $queue = $project->getFullSyncQueue();
        } else {
            $pip = $this->getSyncablePip($options);
            if ($pip === null) {
                return self::EXIT_FAILURE;
            }

            if (isset($options['target'])) {
                if (!$this->isValidTarget($project, $pip, $options['target'])) {
                    return self::EXIT_FAILURE;
                }

                $targets = [$options['target']];
            } else {
                $targets = $pip->getTargets($project);
                if ($targets === []) {
                    $this->error("The PIP '{$pip->pluginName}' has no targets in this project.");

                    return self::EXIT_FAILURE;
                }
            }

            $queue = \array_map(static fn (string $target) => [$pip->pluginName, $target], $targets);
        }

        foreach ($queue as [$pluginName, $target]) {
            if (!$this->syncTarget($project, $pluginName, $target, isset($options['allow-unsafe']))) {
                return self::EXIT_FAILURE;
            }
        }

        if (isset($options['all'])) {
            new SyncPackageVersion(
                $project->getPackage(),
                $project->getPackageArchive()->getPackageInfo('version'),
            )();
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * Syncs a single target by invoking the PIP in child processes until it
     * has completed its work.
     */
    private function syncTarget(DevtoolsProject $project, string $pluginName, string $target, bool $allowUnsafe): bool
    {
        $command = [
            \PHP_BINARY,
            \WCF_DIR . 'devtools.php',
            'invoke',
            "--project={$project->projectID}",
            "--pip={$pluginName}",
            "--target={$target}",
        ];
        if ($allowUnsafe) {
            $command[] = '--allow-unsafe';
        }

        while (true) {
            $start = \microtime(true);

            $process = \proc_open($command, [0 => \STDIN, 1 => \STDOUT, 2 => \STDERR], $pipes);
            if ($process === false) {
                $this->error("Unable to start the process to sync '{$pluginName}' ({$target}).");

                return false;
            }

            $exitCode = \proc_close($process);
            $timeElapsed = \sprintf('%.2fs', \microtime(true) - $start);

            if ($exitCode === self::EXIT_SUCCESS) {
                $this->write("{$pluginName}\t{$target}\t{$timeElapsed}");

                return true;
            } elseif ($exitCode === self::EXIT_INVOKE_AGAIN) {
                $this->write("{$pluginName}\t{$target}\t{$timeElapsed}\t(continuing)");
            } else {
                $this->error("Syncing '{$pluginName}' ({$target}) failed with exit code {$exitCode}.");

                return false;
            }
        }
    }

    /**
     * Invokes the PIP once for a single target.
     *
     * @param array<string, string|true> $options
     */
    private function invoke(array $options): int
    {
        $project = $this->getProject($options['project']);
        if ($project === null) {
            return self::EXIT_FAILURE;
        }

        $pip = $this->getSyncablePip($options);
        if ($pip === null || !$this->isValidTarget($project, $pip, $options['target'])) {
            return self::EXIT_FAILURE;
        }

        $invokeAgain = new SyncPackageInstallationPlugin($project, $pip, $options['target'])();

        return $invokeAgain ? self::EXIT_INVOKE_AGAIN : self::EXIT_SUCCESS;
    }

    /**
     * Resolves the project by its ID, its path, its name or the identifier of
     * its package, in this order. A path must contain a `/` or be `.` or `..`,
     * otherwise a directory in the working directory that happens to be named
     * like a package would shadow the identifier.
     */
    private function getProject(string $value): ?DevtoolsProject
    {
        $projectList = new DevtoolsProjectList();
        $projectList->readObjects();
        $projects = $projectList->getObjects();

        $project = null;
        if (\preg_match('~^[1-9][0-9]*$~', $value)) {
            $project = $projects[(int)$value] ?? null;
        } elseif (\str_contains($value, '/') || $value === '.' || $value === '..') {
            $path = \realpath($value);
            if ($path === false || !\is_dir($path)) {
                $this->error("The directory '{$value}' does not exist.");

                return null;
            }

            $path = FileUtil::addTrailingSlash($path);
            foreach ($projects as $candidate) {
                $candidatePath = \realpath($candidate->path);
                if ($candidatePath !== false && FileUtil::addTrailingSlash($candidatePath) === $path) {
                    $project = $candidate;
                    break;
                }
            }
        } else {
            foreach ($projects as $candidate) {
                if ($candidate->name === $value) {
                    $project = $candidate;
                    break;
                }
            }

            if ($project === null) {
                foreach ($projects as $candidate) {
                    if (
                        $candidate->validate(true) === ''
                        && $candidate->getPackageArchive()->getPackageInfo('name') === $value
                    ) {
                        $project = $candidate;
                        break;
                    }
                }
            }
        }

        if ($project === null) {
            $this->error("Unknown project '{$value}'.");

            return null;
        }

        $error = $project->validate();
        if ($error !== '') {
            // The messages are phrases that may contain markup.
            $error = StringUtil::decodeHTML(\strip_tags($error));
            $this->error("The project '{$project->name}' is invalid: {$error}");

            return null;
        }

        return $project;
    }

    private function getPip(string $pluginName): ?DevtoolsPip
    {
        $packageInstallationPlugin = new PackageInstallationPlugin($pluginName);
        if ($packageInstallationPlugin->isNil()) {
            $this->error("Unknown PIP '{$pluginName}'.");

            return null;
        }

        return new DevtoolsPip($packageInstallationPlugin);
    }

    /**
     * @param array<string, string|true> $options
     */
    private function getSyncablePip(array $options): ?DevtoolsPip
    {
        $pip = $this->getPip($options['pip']);
        if ($pip === null) {
            return null;
        }

        if ($pip->isUnsafe() && !isset($options['allow-unsafe'])) {
            $this->error("The PIP '{$pip->pluginName}' is unsafe and requires --allow-unsafe.");

            return null;
        }

        return $pip;
    }

    private function isValidTarget(DevtoolsProject $project, DevtoolsPip $pip, string $target): bool
    {
        if (!\in_array($target, $pip->getTargets($project), true)) {
            $this->error("Unknown target '{$target}' of the PIP '{$pip->pluginName}'.");

            return false;
        }

        return true;
    }

    /**
     * Checks the required options and the combinations of options that the
     * command does not support.
     *
     * @param array<string, string|true> $options
     */
    private function validateOptions(string $command, array $options): bool
    {
        foreach (self::REQUIRED_OPTIONS[$command] as $name) {
            if (!isset($options[$name])) {
                $this->error("The command '{$command}' requires the option --{$name}.");

                return false;
            }
        }

        if ($command !== 'sync') {
            return true;
        }

        if (isset($options['all'])) {
            if (isset($options['pip']) || isset($options['target']) || isset($options['allow-unsafe'])) {
                $this->error('--all cannot be combined with --pip, --target or --allow-unsafe.');

                return false;
            }
        } elseif (!isset($options['pip'])) {
            $this->error('Either --pip or --all is required.');

            return false;
        }

        return true;
    }

    /**
     * Parses `--name=value` options and `--flag` flags, returns `null` if the
     * arguments are invalid for the command.
     *
     * @param list<string> $arguments
     * @return ?array<string, string|true>
     */
    private function parseOptions(string $command, array $arguments): ?array
    {
        $options = [];
        foreach ($arguments as $argument) {
            if (!\preg_match('~^--([a-z-]+)(?:=(.*))?$~s', $argument, $matches)) {
                $this->error("Unexpected argument '{$argument}'.");

                return null;
            }

            $name = $matches[1];
            if (!\in_array($name, self::COMMANDS[$command], true)) {
                $this->error("The command '{$command}' does not support the option --{$name}.");

                return null;
            }

            if (isset($options[$name])) {
                $this->error("The option --{$name} was given more than once.");

                return null;
            }

            $isFlag = \in_array($name, self::FLAGS, true);
            if ($isFlag === isset($matches[2])) {
                $this->error(
                    $isFlag
                        ? "The option --{$name} does not take a value."
                        : "The option --{$name} requires a value."
                );

                return null;
            }

            $options[$name] = $isFlag ? true : $matches[2];
        }

        return $options;
    }

    private function printUsage(): void
    {
        $this->write(<<<'EOT'
            Usage: php devtools.php <command> [options]

            Commands:
              projects
                  List the projects as their ID, name and path.

              targets --project=<project> [--pip=<pip>]
                  List the targets of all PIPs or of a single PIP.

              sync --project=<project> --all
                  Sync all PIPs in the order of their dependencies, followed by
                  the package version. Unsafe PIPs are never synced.

              sync --project=<project> --pip=<pip> [--target=<target>] [--allow-unsafe]
                  Sync all targets or a single target of a PIP. Unsafe PIPs
                  require --allow-unsafe.

            <project> is the ID, the path or the name of the project, or the
            identifier of its package. A path must contain a '/' or be '.'.

            Exit codes: 0 on success, 1 on failure, 2 on invalid arguments.
            EOT);
    }

    private function printUsageHint(): void
    {
        $this->error("Run 'php devtools.php help' for usage.");
    }

    private function write(string $message): void
    {
        \fwrite(\STDOUT, $message . "\n");
    }

    private function error(string $message): void
    {
        \fwrite(\STDERR, $message . "\n");
    }

    private static function handleUncaughtException(\Throwable $e): never
    {
        \fwrite(\STDERR, (string)$e . "\n");

        exit(self::EXIT_FAILURE);
    }
}

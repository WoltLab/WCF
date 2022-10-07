<?php

namespace wcf\system\package;

use wcf\system\devtools\package\DevtoolsPackageArchive;
use wcf\system\devtools\package\DevtoolsTar;

/**
 * Generates a manifest for the given PackageArchive.
 *
 * The manifest is a structured representation of the functional parts
 * of the archive wherein changes could be relevant to security.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package
 * @since   6.0
 */
final class PackageManifest
{
    private PackageArchive $archive;

    public const SUPPORTED_VERSIONS = [1];

    public const CURRENT_VERSION = 1;

    public function __construct(PackageArchive $archive)
    {
        $this->archive = $archive;
    }

    /**
     * Returns a string containing the manifest version and the manifest's hash.
     *
     * @param $version An item in self::SUPPORTED_VERSIONS.
     */
    public function getHash(int $version = self::CURRENT_VERSION): string
    {
        if ($version === 1) {
            return \sprintf(
                "%d-%s",
                $version,
                \hash('sha256', $this->getManifest($version))
            );
        } elseif (\in_array($version, self::SUPPORTED_VERSIONS)) {
            throw new \LogicException("Unhandled, but supported, manifest version '{$version}'.");
        } else {
            throw new \InvalidArgumentException("Unknown manifest version '{$version}'.");
        }
    }

    /**
     * Returns the archive's manifest.
     *
     * @param $version An element of self::SUPPORTED_VERSIONS.
     */
    public function getManifest(int $version = self::CURRENT_VERSION): string
    {
        if ($version === 1) {
            return $this->getManifestV1();
        } elseif (\in_array($version, self::SUPPORTED_VERSIONS)) {
            throw new \LogicException("Unhandled, but supported, manifest version '{$version}'.");
        } else {
            throw new \InvalidArgumentException("Unknown manifest version '{$version}'.");
        }
    }

    private function getManifestV1(): string
    {
        $requirements = \array_map(static function (array $requirement): string {
            $file = (string)$requirement['file'];

            if ($file === '') {
                throw new \UnexpectedValueException('Expected to see a non-empty file="" attribute for an requirement.');
            }

            return $file;
        }, \array_filter(
            $this->archive->getRequirements(),
            static fn ($requirement) => isset($requirement['file'])
        ));

        $optionals = \array_map(static function (array $optional): string {
            if (!isset($optional['file'])) {
                throw new \UnexpectedValueException('Expected to see a file="" attribute for an optional.');
            }

            $file = (string)$optional['file'];

            if ($file === '') {
                throw new \UnexpectedValueException('Expected to see a non-empty file="" attribute for an optional.');
            }

            return $file;
        }, $this->archive->getOptionals());

        $includedPackages = \array_merge($requirements, $optionals);
        $ignoredFiles = \array_merge($includedPackages, ['package.xml']);

        $manifest = [
            'manifestVersion' => '1',
            'identifier' => $this->archive->getPackageInfo('name'),
            'version' => $this->archive->getPackageInfo('version'),
            'isApplication' => $this->archive->getPackageInfo('isApplication'),
            'displayName' => $this->getDisplayNames(),
            'requirements' => $this->getRequirements(),
            'excludedPackages' => $this->getExcludedPackages(),
            'files' => $this->getFiles($ignoredFiles),
            'install' => $this->getInstallInstructions(),
            'update' => $this->getUpdateInstructions(),
        ];

        if ($this->archive instanceof DevtoolsPackageArchive) {
            $manifest = [
                'Fake Devtools Archive' => 'Fake Devtools Archive',
                ...$manifest,
            ];
        }

        return $this->stringifyV1($manifest);
    }

    private function getDisplayNames(): array
    {
        $displayNames = $this->archive->getPackageInfo('packageName');
        \ksort($displayNames);

        return $displayNames;
    }

    private function getRequirements(): array
    {
        $requirements = $this->archive->getRequirements();

        \usort($requirements, $this->compareByName(...));

        return \array_map(static function (array $requirementData): array {
            unset($requirementData['file']);
            \ksort($requirementData);

            return $requirementData;
        }, $requirements);
    }

    private function getExcludedPackages(): array
    {
        $exclusions = $this->archive->getExcludedPackages();

        \usort($exclusions, $this->compareByName(...));

        return \array_map(static function (array $exclusion): array {
            \ksort($exclusion);

            return $exclusion;
        }, $exclusions);
    }

    private function getFiles(array $ignore = []): array
    {
        $tar = $this->archive->getTar();
        $files = [];
        foreach ($tar->getContentList() as $file) {
            if ($file['type'] !== 'file') {
                continue;
            }
            if (\in_array($file['filename'], $ignore, true)) {
                continue;
            }

            if ($tar instanceof DevtoolsTar) {
                $files[$file['filename']] = 'Fake Devtools Archive';
            } else {
                $files[$file['filename']] = \hash('sha256', $tar->extractToString($file['filename']));
            }
        }
        \ksort($files);

        return $files;
    }

    private function getInstallInstructions(): array
    {
        return $this->cleanInstructions($this->archive->getInstallInstructions());
    }

    private function getUpdateInstructions(): array
    {
        $updateInstructions = $this->archive->getUpdateInstructions();
        \ksort($updateInstructions);

        return \array_map($this->cleanInstructions(...), $updateInstructions);
    }

    private function compareByName(array $a, array $b): int
    {
        return $a['name'] <=> $b['name'];
    }

    private function cleanInstructions(array $instructions): array
    {
        // Note: The $instructions array *must not* be sorted. The order
        // of instructions is important!
        return \array_map(static function ($instruction) {
            unset($instruction['attributes']['type']);
            \ksort($instruction['attributes']);

            return [
                'type' => $instruction['pip'],
                'value' => $instruction['value'],
                'attributes' => $instruction['attributes'],
            ];
        }, $instructions);
    }

    /**
     * Transforms the given $data into a stable, unique string representation.
     *
     * The method takes care to return the same string for identical input data and
     * to return different strings for differing input data.
     *
     * Attention: This method must not be modified. If a format change is required a
     * replacement method must be written.
     *
     * @throws UnexpectedValueException On non-representable data.
     */
    private function stringifyV1(array|string|int $data, int $depth = 0): string
    {
        $indentation = \str_repeat('  ', $depth);

        if (!\is_array($data)) {
            return \sprintf("{$indentation}'%s'\n", $this->escape($data));
        }
        if ($data === []) {
            return \sprintf("{$indentation}%s\n", '[]');
        }

        $result = "";
        $numeric = null;
        $lastNumeric = -1;
        foreach ($data as $key => $value) {
            if (\is_numeric($key)) {
                if ($numeric !== null && !$numeric) {
                    throw new \UnexpectedValueException('Arrays with mixed numeric / string keys are not supported.');
                }
                if ($lastNumeric !== ($key - 1)) {
                    throw new \UnexpectedValueException('Arrays with non-sequential numeric keys are not supported.');
                }

                $numeric = true;
                $lastNumeric = \intval($key);
                $result .= "{$indentation}-";
            } else {
                if ($numeric !== null && $numeric) {
                    throw new \UnexpectedValueException('Arrays with mixed numeric / string keys are not supported.');
                }

                $numeric = false;
                $result .= "{$indentation}'" . $this->escape($key) . "':";
            }

            if (\is_array($value) && $value !== []) {
                $result .= "\n" . $this->stringifyV1($value, $depth + 1);
            } else {
                $result .= " " . $this->stringifyV1($value);
            }
        }

        return $result;
    }

    private function escape(string $v): string
    {
        return \preg_replace_callback('/[^a-zA-Z0-9 \\/\\.:_\\*\\-]/', static function ($matches) {
            return \sprintf("\\x%s", \bin2hex($matches[0]));
        }, $v);
    }
}

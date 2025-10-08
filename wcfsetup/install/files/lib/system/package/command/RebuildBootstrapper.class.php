<?php

namespace wcf\system\package\command;

use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\io\AtomicWriter;
use wcf\system\WCF;

/**
 * Rebuilds the bootstrapping script.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class RebuildBootstrapper
{
    public function __invoke(): void
    {
        $groups = PackageList::getTopologicallySortedPackages();

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $result = "<?php /* {$now->format('c')} */\n\n";
        $result .= <<<'EOT'
            return (function() {
                if (\ENABLE_DEBUG_MODE) {
                    $shuffle = static function (array $array) {
                        \shuffle($array);

                        return $array;
                    };
                } else {
                    $shuffle = static function (array $array) {
                        return $array;
                    };
                }

                return [
            EOT;
        $result .= "\n";

        foreach ($groups as $group) {
            $group = \array_values(\array_filter($group, $this->bootstrapExists(...)));

            if ($group === []) {
                continue;
            }

            if (\count($group) === 1) {
                $package = $group[0];
                $result .= "        require(__DIR__ . '/{$this->getRelativeBootstrapFilename($package)}'),\n";
            } else {
                $result .= "        ...\$shuffle([\n";
                \shuffle($group);
                foreach ($group as $package) {
                    $result .= "            require(__DIR__ . '/{$this->getRelativeBootstrapFilename($package)}'),\n";
                }
                $result .= "        ]),\n";
            }
        }

        $result .= <<<'EOT'
                ];
            })();
            EOT;
        $result .= "\n";

        $writer = new AtomicWriter(WCF::BOOTSTRAP_LOADER);
        $writer->write($result);
        $writer->flush();

        WCF::resetZendOpcache(WCF::BOOTSTRAP_LOADER);
    }

    private function bootstrapExists(Package $package): bool
    {
        return \file_exists($this->getBootstrapFilename($package));
    }

    private function getBootstrapFilename(Package $package): string
    {
        return \WCF_DIR . 'lib/' . $this->getRelativeBootstrapFilename($package);
    }

    private function getRelativeBootstrapFilename(Package $package): string
    {
        return 'bootstrap/' . $package->package . '.php';
    }
}

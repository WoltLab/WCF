<?php

namespace wcf\data\option;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\cache\CacheHandler;
use wcf\system\io\AtomicWriter;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Option      create(array $parameters = [])
 * @method      Option      getDecoratedObject()
 * @mixin       Option
 */
class OptionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * options cache file name
     * @var string
     */
    const FILENAME = 'options.inc.php';

    /**
     * @inheritDoc
     */
    protected static $baseClass = Option::class;

    /**
     * Imports the given options.
     *
     * @param array $options name to value
     */
    public static function import(array $options)
    {
        // get option ids
        $sql = "SELECT  optionName, optionID
                FROM    wcf1_option";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $optionIDs = $statement->fetchMap('optionName', 'optionID');

        $newOptions = [];
        foreach ($options as $name => $value) {
            if (isset($optionIDs[$name])) {
                $newOptions[$optionIDs[$name]] = $value;
            }
        }

        self::updateAll($newOptions);
    }

    /**
     * Updates the values of the given options.
     *
     * @param array $options id to value
     */
    public static function updateAll(array $options)
    {
        $sql = "SELECT  optionID, optionName, optionValue
                FROM    wcf1_option
                WHERE   optionName IN (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['cache_source_type', 'visitor_use_tiny_build']);
        $oldValues = [];
        while ($row = $statement->fetchArray()) {
            $oldValues[$row['optionID']] = $row;
        }

        $sql = "UPDATE  wcf1_option
                SET     optionValue = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $flushCache = false;
        $flushPermissions = false;
        WCF::getDB()->beginTransaction();
        foreach ($options as $id => $value) {
            if (isset($oldValues[$id])) {
                if ($value != $oldValues[$id]['optionValue']) {
                    if ($oldValues[$id]['optionName'] === 'cache_source_type') {
                        $flushCache = true;
                    } else {
                        $flushPermissions = true;
                    }
                }
            }

            $statement->execute([
                $value,
                $id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        // force a cache reset if options were changed
        self::resetCache();

        // flush entire cache, as the CacheSource was changed
        if ($flushCache) {
            // flush caches (in case register_shutdown_function gets not properly called)
            CacheHandler::getInstance()->flushAll();
            UserStorageHandler::getInstance()->clear();

            // flush cache before finishing request to flush caches created after this was executed
            \register_shutdown_function(static function () {
                CacheHandler::getInstance()->flushAll();
                UserStorageHandler::getInstance()->clear();
            });
        } elseif ($flushPermissions) {
            // flush permissions if accelerated visitor mode was toggled
            UserGroupEditor::resetCache();
        }
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        // reset cache
        OptionCacheBuilder::getInstance()->reset();

        // reset options.inc.php files
        self::rebuild();
    }

    /**
     * Rebuilds the option file.
     */
    public static function rebuild()
    {
        $writer = new AtomicWriter(WCF_DIR . 'options.inc.php');

        // file header
        $writer->write("<?php\n/**\n* generated at " . \gmdate('r') . "\n*/\n");

        // Secret options cannot be enabled through the regular options, they need to be manually
        // defined in the Core's `config.inc.php` to be activated.
        $enableEnterpriseMode = new Option(
            null,
            ['optionName' => 'enable_enterprise_mode', 'optionType' => 'integer', 'optionValue' => 0]
        );
        $secretOptions = [
            $enableEnterpriseMode->getConstantName() => $enableEnterpriseMode,
        ];

        // get all options
        $options = $secretOptions + Option::getOptions();
        foreach ($options as $optionName => $option) {
            if ($optionName === 'WOLTLAB_BRANDING') {
                continue;
            }

            $writeValue = $option->optionValue;
            if ($writeValue === null) {
                $writeValue = "''";
            } elseif ($option->optionType == 'boolean' || $option->optionType == 'integer') {
                $writeValue = \intval($option->optionValue);
            } else {
                $writeValue = "'" . \addcslashes($option->optionValue, "'\\") . "'";
            }

            $writer->write("if (!\\defined('{$optionName}')) \\define('{$optionName}', {$writeValue});\n");
        }
        unset($options);

        // add a pseudo option that indicates that option file has been written properly
        $writer->write("if (!\\defined('WCF_OPTION_INC_PHP_SUCCESS')) \\define('WCF_OPTION_INC_PHP_SUCCESS', true);");

        // file footer
        $writer->write("\n");
        $writer->flush();
        $writer->close();

        FileUtil::makeWritable(WCF_DIR . 'options.inc.php');
        WCF::resetZendOpcache(WCF_DIR . 'options.inc.php');
    }
}

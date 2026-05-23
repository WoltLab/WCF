<?php

namespace wcf\data\option;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\io\AtomicWriter;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Option
 * @extends DatabaseObjectEditor<Option>
 * @implements IEditableCachedObject<Option>
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
     * @param array<string, string|int|float> $options name to value
     * @return void
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
     * @param array<int, string|int|float> $options id to value
     * @return void
     */
    public static function updateAll(array $options)
    {
        $sql = "SELECT  optionID, optionName, optionValue
                FROM    wcf1_option
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['visitor_use_tiny_build']);
        $oldValues = [];
        while ($row = $statement->fetchArray()) {
            $oldValues[$row['optionID']] = $row;
        }

        $sql = "UPDATE  wcf1_option
                SET     optionValue = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $flushPermissions = false;
        WCF::getDB()->beginTransaction();
        foreach ($options as $id => $value) {
            if (isset($oldValues[$id]) && $value != $oldValues[$id]['optionValue']) {
                $flushPermissions = true;
            }

            $statement->execute([
                $value,
                $id,
            ]);
        }
        WCF::getDB()->commitTransaction();

        // force a cache reset if options were changed
        self::resetCache();

        if ($flushPermissions) {
            // flush permissions if accelerated visitor mode was toggled
            UserGroupEditor::resetCache();
        }
    }

    #[\Override]
    public static function resetCache()
    {
        // reset cache
        OptionCacheBuilder::getInstance()->reset();

        // reset options.inc.php files
        self::rebuild();
    }

    /**
     * Rebuilds the option file.
     *
     * @return void
     */
    public static function rebuild()
    {
        $writer = new AtomicWriter(\WCF_DIR . 'options.inc.php');

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

        FileUtil::makeWritable(\WCF_DIR . 'options.inc.php');
        WCF::resetZendOpcache(\WCF_DIR . 'options.inc.php');
    }
}

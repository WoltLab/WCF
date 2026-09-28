<?php

namespace wcf\data\reaction\type;

use wcf\data\CollectionDatabaseObject;
use wcf\data\file\File;
use wcf\data\ITitledObject;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;

/**
 * Represents a reaction type.
 *
 * The localized title is stored in the `wcf1_reaction_type_l10n` table.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @property-read   int     $reactionTypeID unique id of the reaction type
 * @property-read   int     $showOrder      position of the reaction type in relation to the other reaction types
 * @property-read   ?int    $iconFileID     id of the file of the icon or `null` if the file has been deleted
 * @property-read   0|1     $isAssignable   `1`, if the reaction can be assigned, otherwise `0`
 * @property-read   ?string $l10nIdentifier name of the language variable the localized title is derived from, `null` for reaction types created by an administrator
 *
 * @extends CollectionDatabaseObject<ReactionTypeCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class ReactionType extends CollectionDatabaseObject implements ITitledObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'reactionTypeID';

    /**
     * Cache for the rendered icons.
     * @var array<int, string>
     */
    private array $renderedIcons = [];

    private ?File $iconFile = null;

    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'title');
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'title') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Renders the reaction icon.
     *
     * @return  string
     */
    public function renderIcon()
    {
        if (!isset($this->renderedIcons[$this->reactionTypeID])) {
            $this->renderedIcons[$this->reactionTypeID] =  WCF::getTPL()->render('wcf', 'reactionTypeImage', [
                'reactionType' => $this,
            ]);
        }

        return $this->renderedIcons[$this->reactionTypeID];
    }

    /**
     * Returns the url to the icon for this reaction.
     *
     * @return string
     */
    public function getIconPath()
    {
        $file = $this->getIconFile();
        if ($file === null) {
            // The file is deleted immediately from within the form, leaving the
            // reaction type without an icon until the form is saved. Mirrors the
            // placeholder for legacy `.icon` elements to make the gap obvious.
            return 'data:image/svg+xml;base64,' . \base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">'
                    . '<rect width="24" height="24" rx="3" fill="#f0c"/>'
                    . '<text x="12" y="12" fill="#fff" font-family="sans-serif" font-size="18" text-anchor="middle" dominant-baseline="central">?</text>'
                    . '</svg>'
            );
        }

        return $file->getFullSizeImageSource() ?? $file->getLink();
    }

    /**
     * @since 6.3
     */
    public function getIconFile(): ?File
    {
        if ($this->iconFileID === null) {
            return null;
        }

        $this->iconFile ??= FileRuntimeCache::getInstance()->getObject($this->iconFileID);

        return $this->iconFile;
    }

    /**
     * @since 6.3
     */
    public function setIconFile(File $file): void
    {
        \assert($file->fileID === $this->iconFileID);

        $this->iconFile = $file;
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_reaction_type',
            'wcf1_reaction_type_l10n',
            'reactionTypeID',
            ['title'],
            'l10nIdentifier',
            ['title' => ''],
        );
    }
}

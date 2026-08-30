<?php

namespace wcf\data\bbcode\attribute;

use wcf\data\bbcode\BBCode;
use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting bbcode attributes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<BBCodeAttribute>
 */
final class BBCodeAttributeBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the bbcode the attribute belongs to.
     */
    public function setBBCode(BBCode $bbcode): static
    {
        return $this->setBBCodeID($bbcode->bbcodeID);
    }

    /**
     * Sets the id of the bbcode the attribute belongs to.
     */
    public function setBBCodeID(int $bbcodeID): static
    {
        $this->properties['bbcodeID'] = $bbcodeID;

        return $this;
    }

    /**
     * Sets the number of the attribute, determining the position of the
     * attribute within the bbcode.
     */
    public function setAttributeNo(int $attributeNo): static
    {
        $this->properties['attributeNo'] = $attributeNo;

        return $this;
    }

    /**
     * Sets the html code that is used to render the attribute, an empty string
     * if no such html code exists.
     */
    public function setAttributeHtml(string $attributeHtml): static
    {
        $this->properties['attributeHtml'] = $attributeHtml;

        return $this;
    }

    /**
     * Sets the regular expression that is used to validate the attribute's
     * value, an empty string if no such regular expression exists.
     */
    public function setValidationPattern(string $validationPattern): static
    {
        $this->properties['validationPattern'] = $validationPattern;

        return $this;
    }

    /**
     * Sets whether the attribute is required by the bbcode.
     */
    public function setRequired(bool $required): static
    {
        $this->properties['required'] = $required ? 1 : 0;

        return $this;
    }

    /**
     * Sets whether the bbcode's content is used as the attribute's value.
     */
    public function setUseText(bool $useText): static
    {
        $this->properties['useText'] = $useText ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['bbcodeID', 'attributeNo'];
    }
}

<?php

namespace wcf\system\form\builder\wysiwyg;

use wcf\data\smiley\Smiley;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormNode;
use wcf\system\template\SharedTemplateEngine;

/**
 * Implementation of a form field for the list smilies of a certain category used by a wysiwyg
 * form container.
 *
 * This is no really a form field in that it does not read any data but only prints data.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class WysiwygSmileyFormNode implements IFormChildNode
{
    use TFormChildNode;
    use TFormNode;

    /**
     * list of available smilies
     * @var Smiley[]
     */
    protected $smilies = [];

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return SharedTemplateEngine::getInstance()->fetch('shared_wysiwygSmileyFormNode', 'wcf', [
            'node' => $this,
        ]);
    }

    /**
     * Returns the list of available smilies.
     *
     * @return  Smiley[]
     */
    public function getSmilies()
    {
        return $this->smilies;
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        // does nothing
    }

    /**
     * Sets the list of available smilies.
     *
     * @param Smiley[] $smilies available smilies
     * @return  WysiwygSmileyFormNode       this form field
     */
    public function smilies(array $smilies)
    {
        foreach ($smilies as $smiley) {
            if (!\is_object($smiley)) {
                throw new \InvalidArgumentException(
                    "Given value array contains invalid value of type " . \gettype($smiley) . " for field '{$this->getId()}'."
                );
            } elseif (!($smiley instanceof Smiley)) {
                throw new \InvalidArgumentException(
                    "Given value array contains invalid object of class " . \get_class($smiley) . " for field '{$this->getId()}'."
                );
            }
        }

        $this->smilies = $smilies;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // does nothing
    }
}

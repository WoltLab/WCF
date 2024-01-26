<?php

namespace wcf\system\form\builder\button;

use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormElement;
use wcf\system\WCF;

/**
 * Default implementation of a form button.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class FormButton implements IFormButton
{
    use TFormChildNode;
    use TFormElement;

    /**
     * access key for this form button or `null` if no access key has been set
     * @var null|string
     */
    protected $accessKey;

    /**
     * `true` this button is an `input[type=submit]` element and `false` if it is a `button` element
     * @var bool
     */
    protected $submit = false;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_formButton';

    /**
     * @inheritDoc
     */
    public function accessKey($accessKey = null)
    {
        // the value [of the accesskey attribute] must be an ordered set of unique
        // space-separated tokens that are case-sensitive, each of which must be exactly
        // one Unicode code point in length.
        // Source: https://www.w3.org/TR/html50/editing.html#the-accesskey-attribute
        if ($accessKey !== null) {
            $splitAccessKey = \array_unique(\explode(' ', $accessKey));
            foreach ($splitAccessKey as $accessKey) {
                if (\mb_strlen($accessKey) !== 1) {
                    throw new \InvalidArgumentException(
                        "The given access key contains an access key longer than one character: '{$accessKey}' for buttom '{$this->getId()}'."
                    );
                }
            }
        }

        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return WCF::getTPL()->fetch(
            $this->templateName,
            'wcf',
            [
                'button' => $this,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function isSubmit()
    {
        return $this->submit;
    }

    /**
     * @inheritDoc
     */
    public function submit($submit = true)
    {
        $this->submit = $submit;

        if ($this->isSubmit() && $this->getLabel() === null) {
            $this->label('wcf.global.button.submit');
        }

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

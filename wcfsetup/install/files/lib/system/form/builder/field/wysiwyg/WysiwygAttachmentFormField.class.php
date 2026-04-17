<?php

namespace wcf\system\form\builder\field\wysiwyg;

use wcf\system\attachment\AttachmentHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\system\WCF;

/**
 * Represents the form field to manage attachments for a wysiwyg form container.
 *
 * If no attachment handler has been set, this field is not available.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class WysiwygAttachmentFormField extends AbstractFormField
{
    use TWysiwygFormNode;

    /**
     * attachment handler
     * @var ?AttachmentHandler
     */
    protected $attachmentHandler;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Wysiwyg/Attachment';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_wysiwygAttachmentFormField';

    public function __construct()
    {
        $this->addClass('wide');
    }

    /**
     * Sets the attachment handler object for the uploaded attachments. If `null` is given,
     * the previously set attachment handler is unset.
     *
     * For the initial attachment handler set by this method, the temporary hashes will be
     * automatically set by either reading them from the session variables if the form handles
     * AJAX requests or by creating a new one. If the temporary hashes are read from session,
     * the session variable will be unregistered afterwards.
     *
     * @return WysiwygAttachmentFormField
     */
    public function attachmentHandler(?AttachmentHandler $attachmentHandler = null)
    {
        if ($attachmentHandler !== null) {
            if ($this->attachmentHandler === null) {
                $tmpHash = \sha1(\implode("\0", [
                    $this->getId(),
                    $attachmentHandler->getObjectType()->objectType,
                    $attachmentHandler->getParentObjectID(),
                    $attachmentHandler->getObjectID(),
                    WCF::getUser()->userID ?: WCF::getSession()->sessionID,
                ]));

                if ($this->getDocument()->isAjax()) {
                    /** @deprecated 5.5 see QuickReplyManager::setTmpHash() */
                    $sessionTmpHash = WCF::getSession()->getVar('__wcfAttachmentTmpHash');
                    if ($sessionTmpHash !== null) {
                        $tmpHash = $sessionTmpHash;

                        WCF::getSession()->unregister('__wcfAttachmentTmpHash');
                    }
                }

                $attachmentHandler->setTmpHashes([$tmpHash]);
            } else {
                // preserve temporary hashes
                $attachmentHandler->setTmpHashes($this->attachmentHandler->getTmpHashes());
            }
        }

        $this->attachmentHandler = $attachmentHandler;

        return $this;
    }

    /**
     * Returns the attachment handler object for the uploaded attachments or `null` if no attachment
     * upload is supported.
     *
     * @return ?AttachmentHandler
     */
    public function getAttachmentHandler()
    {
        return $this->attachmentHandler;
    }

    #[\Override]
    public function hasSaveValue()
    {
        return false;
    }

    #[\Override]
    public function isAvailable()
    {
        return parent::isAvailable()
            && $this->getAttachmentHandler() !== null
            && $this->getAttachmentHandler()->canUpload();
    }

    #[\Override]
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            $this->getId(),
            function (IFormDocument $document, array $parameters) {
                if ($this->getAttachmentHandler() !== null) {
                    $parameters[$this->getWysiwygId() . '_attachmentHandler'] = $this->getAttachmentHandler();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId() . '_tmpHash')) {
            $tmpHash = $this->getDocument()->getRequestData($this->getPrefixedId() . '_tmpHash');
            if (\is_string($tmpHash)) {
                $this->getAttachmentHandler()->setTmpHashes([$tmpHash]);
            } elseif (\is_array($tmpHash)) {
                $this->getAttachmentHandler()->setTmpHashes($tmpHash);
            }
        }

        return $this;
    }
}

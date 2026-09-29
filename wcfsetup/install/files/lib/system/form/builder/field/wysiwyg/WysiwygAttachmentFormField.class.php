<?php

namespace wcf\system\form\builder\field\wysiwyg;

use ParagonIE\ConstantTime\Hex;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TWysiwygFormNode;
use wcf\system\session\SessionHandler;
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
     */
    protected ?AttachmentHandler $attachmentHandler = null;

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
     */
    public function attachmentHandler(?AttachmentHandler $attachmentHandler = null): static
    {
        if ($attachmentHandler !== null) {
            if ($this->attachmentHandler === null) {
                if (WCF::getUser()->isGuest()) {
                    // Reading the session id would start an on-demand session merely by rendering
                    // the form. The random value is carried by the form instead, at the expense of
                    // the uploads being lost when the guest reloads the page.
                    $identifier = SessionHandler::hasOnDemandGuestSessions()
                        ? Hex::encode(\random_bytes(20))
                        : WCF::getSession()->sessionID;
                } else {
                    $identifier = WCF::getUser()->userID;
                }

                $tmpHash = \sha1(\implode("\0", [
                    $this->getId(),
                    $attachmentHandler->getObjectType()->objectType,
                    $attachmentHandler->getParentObjectID(),
                    $attachmentHandler->getObjectID(),
                    $identifier,
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
     */
    public function getAttachmentHandler(): ?AttachmentHandler
    {
        return $this->attachmentHandler;
    }

    #[\Override]
    public function hasSaveValue(): bool
    {
        return false;
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return parent::isAvailable()
            && $this->getAttachmentHandler() !== null
            && $this->getAttachmentHandler()->canUpload();
    }

    #[\Override]
    public function populate(): static
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
    public function readValue(): static
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

<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\ContactRecipientEditForm;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\event\gridView\admin\ContactRecipientGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\I18nTextFilter;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\ObjectIdFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\EmailColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\ContactRecipientInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;

/**
 * Grid view for the list of contact recipients.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<ContactRecipient, ContactRecipientList>
 */
final class ContactRecipientGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("recipientID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),
            GridViewColumn::for("name")
                ->label("wcf.global.name")
                ->filter(new I18nTextFilter())
                ->titleColumn()
                ->renderer(new PhraseColumnRenderer())
                ->sortable(sortByDatabaseColumn: $this->subqueryName()),
            GridViewColumn::for("email")
                ->label("wcf.user.email")
                ->renderer(new EmailColumnRenderer())
                ->sortable(),
            GridViewColumn::for("showOrder")
                ->label("wcf.acp.customOption.showOrder")
                ->filter(new NumericFilter())
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
        ]);

        $provider = new ContactRecipientInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(ContactRecipientEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                "isDisabled",
                "core/contact/recipients/%s/enable",
                "core/contact/recipients/%s/disable"
            )
        );

        $this->addRowLink(new GridViewRowLink(ContactRecipientEditForm::class));

        $this->setDefaultSortField("showOrder");
        $this->setDefaultSortOrder("ASC");
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_CONTACT_FORM
            && WCF::getSession()->getPermission("admin.contact.canManageContactForm");
    }

    #[\Override]
    protected function createObjectList(): ContactRecipientList
    {
        return new ContactRecipientList();
    }

    private function subqueryName(): string
    {
        $languageID = WCF::getLanguage()->languageID;

        return "
            COALESCE((
                SELECT languageItemValue
                FROM   wcf1_language_item
                WHERE  languageItem = contact_recipient.name
                AND    languageID = {$languageID}
            ), contact_recipient.name)
        ";
    }

    #[\Override]
    protected function getInitializedEvent(): ContactRecipientGridViewInitialized
    {
        return new ContactRecipientGridViewInitialized($this);
    }
}

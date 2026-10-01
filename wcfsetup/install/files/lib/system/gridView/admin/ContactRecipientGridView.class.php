<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\ContactRecipientEditForm;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\L10nContactRecipientList;
use wcf\event\gridView\admin\ContactRecipientGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\EmailColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\ContactRecipientInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\L10nTextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of contact recipients.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<ContactRecipient, L10nContactRecipientList>
 */
final class ContactRecipientGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("recipientID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("name")
                ->label("wcf.global.name")
                ->filter(new L10nTextFilter(
                    ContactRecipient::getL10nDefinition(),
                    'name',
                    'name',
                    'wcf.global.name',
                ))
                ->titleColumn()
                ->sortable(sortByDatabaseColumn: 'name'),
            GridViewColumn::for("email")
                ->label("wcf.user.email")
                ->renderer(new EmailColumnRenderer())
                ->sortable(sortByDatabaseColumn: $this->getEmailSortExpression()),
            GridViewColumn::for("showOrder")
                ->label("wcf.acp.customOption.showOrder")
                ->filter(IntegerFilter::class)
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
                'enable',
                'core/contact/recipients/%s/enable',
                'core/contact/recipients/%s/disable'
            )
        );

        $this->addRowLink(new GridViewRowLink(ContactRecipientEditForm::class));

        $this->setDefaultSortField("showOrder");
        $this->setDefaultSortOrder("ASC");
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_CONTACT_FORM !== 0
            && WCF::getSession()->hasPermission("admin.contact.canManageContactForm");
    }

    #[\Override]
    protected function createObjectList(): L10nContactRecipientList
    {
        return new L10nContactRecipientList();
    }

    private function getEmailSortExpression(): string
    {
        // The administrator recipient stores an empty email address and is
        // displayed with `MAIL_ADMIN_ADDRESS`, which cannot be bound in `ORDER BY`.
        $administratorEmail = WCF::getDB()->escapeString(\MAIL_ADMIN_ADDRESS);

        return "CASE
            WHEN contact_recipient.isAdministrator = 1 THEN '{$administratorEmail}'
            ELSE email
        END";
    }

    #[\Override]
    protected function getInitializedEvent(): ContactRecipientGridViewInitialized
    {
        return new ContactRecipientGridViewInitialized($this);
    }
}

<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\data\DatabaseObject;
use wcf\data\user\authentication\failure\UserAuthenticationFailure;
use wcf\data\user\authentication\failure\UserAuthenticationFailureList;
use wcf\event\gridView\admin\UserAuthenticationFailureGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\IpAddressColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\view\filter\IpAddressFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of user authentication failures.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<UserAuthenticationFailure, UserAuthenticationFailureList>
 */
final class UserAuthenticationFailureGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("failureID")
                ->label("wcf.global.objectID")
                ->sortable()
                ->renderer(new ObjectIdColumnRenderer()),
            GridViewColumn::for("environment")
                ->label("wcf.acp.user.authentication.failure.environment")
                ->sortable()
                ->filter(
                    new SelectFilter(
                        [
                            'user' => "wcf.acp.user.authentication.failure.environment.user",
                            'admin' => "wcf.acp.user.authentication.failure.environment.admin",
                        ],
                        "environment",
                        "wcf.acp.user.authentication.failure.environment"
                    )
                )
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserAuthenticationFailure);

                            return WCF::getLanguage()->get(
                                "wcf.acp.user.authentication.failure.environment." . $row->environment
                            );
                        }
                    }
                ),
            GridViewColumn::for("userID")
                ->label("wcf.user.username")
                ->titleColumn()
                ->filter(TextFilter::class)
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->sortable(),
            GridViewColumn::for("time")
                ->label("wcf.acp.user.authentication.failure.time")
                ->filter(TimeFilter::class)
                ->renderer(new TimeColumnRenderer())
                ->sortable(),
            GridViewColumn::for("validationError")
                ->label("wcf.acp.user.authentication.failure.validationError")
                ->filter(
                    new SelectFilter(
                        [
                            'invalidPassword' => "wcf.acp.user.authentication.failure.validationError.invalidPassword",
                            "invalidUsername" => "wcf.acp.user.authentication.failure.validationError.invalidUsername",
                        ],
                        "validationError",
                        "wcf.acp.user.authentication.failure.validationError"
                    )
                )
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserAuthenticationFailure);

                            return WCF::getLanguage()->get(
                                "wcf.acp.user.authentication.failure.validationError." . $row->validationError
                            );
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for("ipAddress")
                ->label("wcf.user.ipAddress")
                ->filter(IpAddressFilter::class)
                ->renderer(new IpAddressColumnRenderer())
                ->sortable(),
            GridViewColumn::for("userAgent")
                ->label("wcf.user.userAgent")
                ->filter(TextFilter::class)
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer(75))
                ->sortable(),
        ]);

        $this->setDefaultSortField("time");
        $this->setDefaultSortOrder("DESC");
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \ENABLE_USER_AUTHENTICATION_FAILURE
            && WCF::getSession()->getPermission('admin.management.canViewLog');
    }

    #[\Override]
    protected function createObjectList(): UserAuthenticationFailureList
    {
        return new UserAuthenticationFailureList();
    }

    #[\Override]
    protected function getInitializedEvent(): UserAuthenticationFailureGridViewInitialized
    {
        return new UserAuthenticationFailureGridViewInitialized($this);
    }
}

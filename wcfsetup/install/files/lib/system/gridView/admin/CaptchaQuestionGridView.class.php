<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\CaptchaQuestionEditForm;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\I18nCaptchaQuestionList;
use wcf\event\gridView\admin\CaptchaQuestionGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\CaptchaQuestionInteractions;
use wcf\system\interaction\bulk\admin\CaptchaQuestionBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of user ranks.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<CaptchaQuestion, I18nCaptchaQuestionList>
 */
final class CaptchaQuestionGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('questionID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('question')
                ->label('wcf.acp.captcha.question.question')
                ->titleColumn()
                ->filter(I18nTextFilter::class)
                ->sortable(sortByDatabaseColumn: 'questionI18n'),
            GridViewColumn::for('views')
                ->label('wcf.acp.captcha.question.views')
                ->sortable()
                ->filter(IntegerFilter::class),
            GridViewColumn::for('correctSubmissions')
                ->label('wcf.acp.captcha.question.correctSubmissions')
                ->sortable()
                ->filter(IntegerFilter::class),
            GridViewColumn::for('incorrectSubmissions')
                ->label('wcf.acp.captcha.question.incorrectSubmissions')
                ->sortable()
                ->filter(IntegerFilter::class),
        ]);

        $provider = new CaptchaQuestionInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(CaptchaQuestionEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new CaptchaQuestionBulkInteractions());

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/captchas/questions/%s/enable',
                'core/captchas/questions/%s/disable'
            )
        );

        $this->setDefaultSortField('questionID');
        $this->addRowLink(new GridViewRowLink(CaptchaQuestionEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.captcha.canManageCaptchaQuestion');
    }

    #[\Override]
    protected function createObjectList(): I18nCaptchaQuestionList
    {
        return new I18nCaptchaQuestionList();
    }

    #[\Override]
    protected function getInitializedEvent(): CaptchaQuestionGridViewInitialized
    {
        return new CaptchaQuestionGridViewInitialized($this);
    }
}

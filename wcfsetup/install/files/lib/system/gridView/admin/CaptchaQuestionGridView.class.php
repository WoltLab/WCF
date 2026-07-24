<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\CaptchaQuestionEditForm;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\L10nCaptchaQuestionList;
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
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\L10nTextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of user ranks.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<CaptchaQuestion, L10nCaptchaQuestionList>
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
                ->filter(new L10nTextFilter(
                    CaptchaQuestion::getL10nDefinition(),
                    'question',
                    'question',
                    'wcf.acp.captcha.question.question',
                ))
                ->sortable(sortByDatabaseColumn: 'question'),
            GridViewColumn::for('views')
                ->label('wcf.acp.captcha.question.views')
                ->sortable(defaultSortOrder: 'DESC')
                ->filter(IntegerFilter::class),
            GridViewColumn::for('correctSubmissions')
                ->label('wcf.acp.captcha.question.correctSubmissions')
                ->sortable(defaultSortOrder: 'DESC')
                ->filter(IntegerFilter::class),
            GridViewColumn::for('incorrectSubmissions')
                ->label('wcf.acp.captcha.question.incorrectSubmissions')
                ->sortable(defaultSortOrder: 'DESC')
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
        return WCF::getSession()->hasPermission('admin.captcha.canManageCaptchaQuestion');
    }

    #[\Override]
    protected function createObjectList(): L10nCaptchaQuestionList
    {
        return new L10nCaptchaQuestionList();
    }

    #[\Override]
    protected function getInitializedEvent(): CaptchaQuestionGridViewInitialized
    {
        return new CaptchaQuestionGridViewInitialized($this);
    }
}

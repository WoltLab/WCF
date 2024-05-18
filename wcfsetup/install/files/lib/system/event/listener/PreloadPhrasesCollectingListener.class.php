<?php

namespace wcf\system\event\listener;

use wcf\event\language\PreloadPhrasesCollecting;

/**
 * Registers a set of default phrases for preloading.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class PreloadPhrasesCollectingListener
{
    public function __invoke(PreloadPhrasesCollecting $event): void
    {
        $event->preload('wcf.button.delete.confirmMessage');

        $event->preload('wcf.clipboard.item.mark');
        $event->preload('wcf.clipboard.item.markAll');
        $event->preload('wcf.clipboard.item.unmarkAll');

        $event->preload('wcf.date.firstDayOfTheWeek');
        $event->preload('wcf.date.hour');
        $event->preload('wcf.date.minute');
        $event->preload('wcf.date.relative.now');

        $event->preload('wcf.date.datePicker');
        $event->preload('wcf.date.datePicker.hour');
        $event->preload('wcf.date.datePicker.minute');
        $event->preload('wcf.date.datePicker.month');
        $event->preload('wcf.date.datePicker.nextMonth');
        $event->preload('wcf.date.datePicker.previousMonth');
        $event->preload('wcf.date.datePicker.year');
        $event->preload('wcf.date.datePicker.clear');
        $event->preload('wcf.date.datePicker.time');
        $event->preload('wcf.date.datePicker.time.clear');

        $event->preload('wcf.dialog.button.cancel');
        $event->preload('wcf.dialog.button.close');
        $event->preload('wcf.dialog.button.primary');
        $event->preload('wcf.dialog.button.primary.confirm');
        $event->preload('wcf.dialog.button.primary.delete');
        $event->preload('wcf.dialog.button.primary.restore');
        $event->preload('wcf.dialog.button.primary.submit');
        $event->preload('wcf.dialog.confirmation.cannotBeUndone');
        $event->preload('wcf.dialog.confirmation.delete');
        $event->preload('wcf.dialog.confirmation.delete.indeterminate');
        $event->preload('wcf.dialog.confirmation.softDelete');
        $event->preload('wcf.dialog.confirmation.softDelete.indeterminate');
        $event->preload('wcf.dialog.confirmation.restore');
        $event->preload('wcf.dialog.confirmation.restore.indeterminate');
        $event->preload('wcf.dialog.confirmation.reason');
        $event->preload('wcf.dialog.confirmation.reason.optional');

        $event->preload('wcf.global.button.add');
        $event->preload('wcf.global.button.cancel');
        $event->preload('wcf.global.button.close');
        $event->preload('wcf.global.button.collapsible');
        $event->preload('wcf.global.button.delete');
        $event->preload('wcf.global.button.disable');
        $event->preload('wcf.global.button.disabledI18n');
        $event->preload('wcf.global.button.edit');
        $event->preload('wcf.global.button.enable');
        $event->preload('wcf.global.button.hide');
        $event->preload('wcf.global.button.hideMenu');
        $event->preload('wcf.global.button.insert');
        $event->preload('wcf.global.button.markAsRead');
        $event->preload('wcf.global.button.markAllAsRead');
        $event->preload('wcf.global.button.more');
        $event->preload('wcf.global.button.next');
        $event->preload('wcf.global.button.preview');
        $event->preload('wcf.global.button.reset');
        $event->preload('wcf.global.button.rss');
        $event->preload('wcf.global.button.save');
        $event->preload('wcf.global.button.search');
        $event->preload('wcf.global.button.showMenu');
        $event->preload('wcf.global.button.submit');
        $event->preload('wcf.global.button.upload');

        $event->preload('wcf.global.confirmation.cancel');
        $event->preload('wcf.global.confirmation.confirm');
        $event->preload('wcf.global.confirmation.title');

        $event->preload('wcf.global.error.ajax.network');
        $event->preload('wcf.global.error.timeout');
        $event->preload('wcf.global.error.title');

        $event->preload('wcf.global.form.error.empty');
        $event->preload('wcf.global.form.error.greaterThan');
        $event->preload('wcf.global.form.error.lessThan');
        $event->preload('wcf.global.form.input.maxItems');
        $event->preload('wcf.global.form.error.multilingual');
        $event->preload('wcf.global.form.password.button.hide');
        $event->preload('wcf.global.form.password.button.show');

        $event->preload('wcf.global.language.noSelection');
        $event->preload('wcf.global.loading');
        $event->preload('wcf.global.noSelection');
        $event->preload('wcf.global.page.next');
        $event->preload('wcf.global.page.pagination');
        $event->preload('wcf.global.page.previous');
        $event->preload('wcf.global.reason');
        $event->preload('wcf.global.reason.optional');
        $event->preload('wcf.global.scrollUp');
        $event->preload('wcf.global.select');
        $event->preload('wcf.global.success');
        $event->preload('wcf.global.success.add');
        $event->preload('wcf.global.success.edit');

        $event->preload('wcf.global.rss.accessToken.info');
        $event->preload('wcf.global.rss.copy');
        $event->preload('wcf.global.rss.copy.success');
        $event->preload('wcf.global.rss.withoutAccessToken');
        $event->preload('wcf.global.rss.withAccessToken');

        $event->preload('wcf.like.button.dislike');
        $event->preload('wcf.like.button.like');
        $event->preload('wcf.like.details');
        $event->preload('wcf.like.summary');
        $event->preload('wcf.like.tooltip');

        $event->preload('wcf.menu.page');
        $event->preload('wcf.menu.page.button.toggle');
        $event->preload('wcf.menu.user');

        $event->preload('wcf.message.share');
        $event->preload('wcf.message.share.copy');
        $event->preload('wcf.message.share.copy.success');
        $event->preload('wcf.message.share.nativeShare');
        $event->preload('wcf.message.share.permalink');
        $event->preload('wcf.message.share.permalink.bbcode');
        $event->preload('wcf.message.share.permalink.html');
        $event->preload('wcf.message.share.socialMedia');

        $event->preload('wcf.moderation.report.reportContent');

        $event->preload('wcf.page.jumpTo');
        $event->preload('wcf.page.jumpTo.description');
        $event->preload('wcf.page.jumpTo.pageNo');
        $event->preload('wcf.page.pageNo');
        $event->preload('wcf.page.pagePosition');
        $event->preload('wcf.page.pagination');

        $event->preload('wcf.reactions.react');
        $event->preload('wcf.reactions.summary.listReactions');

        $event->preload('wcf.style.changeStyle');

        $event->preload('wcf.upload.error.fileExtensionNotPermitted');
        $event->preload('wcf.upload.error.fileSizeTooLarge');
        $event->preload('wcf.upload.error.maximumCountReached');

        $event->preload('wcf.user.activityPoint');
        $event->preload('wcf.user.language');
        $event->preload('wcf.user.panel.settings');
        $event->preload('wcf.user.panel.showAll');
        $event->preload('wcf.user.button.follow');
        $event->preload('wcf.user.button.unfollow');
        $event->preload('wcf.user.button.ignore');
        $event->preload('wcf.user.button.unignore');
    }
}

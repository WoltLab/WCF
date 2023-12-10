{capture assign='__userNotice'}
	{if OFFLINE && $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
		<woltlab-core-notice type="warning">
			<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
			<div>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|phrase}{else}{@OFFLINE_MESSAGE|phrase|newlineToBreak}{/if}</div>
		</woltlab-core-notice>
	{/if}

	{if $templateName != 'accountManagement' && $__wcf->user->userID && $__wcf->user->quitStarted > 0}
		<woltlab-core-notice type="warning">{lang}wcf.user.quit.active{/lang}</woltlab-core-notice>
	{/if}
	
	{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage') && $__wcf->getAvailableUpdates() && SHOW_UPDATE_NOTICE_FRONTEND}
		<woltlab-core-notice type="info">{lang}wcf.page.availableUpdates{/lang}</woltlab-core-notice>
	{/if}
	
	{if $templateName != 'registerNewActivationCode' && $templateName != 'registerActivation' && $templateName != 'register' && $templateName != 'authFlowRedirect' && $__wcf->user->getBlacklistMatches()|empty}
		{if $__wcf->user->requiresEmailActivation()}
			<woltlab-core-notice type="warning">{lang}wcf.user.register.needActivation{/lang}</woltlab-core-notice>
		{elseif $__wcf->user->requiresAdminActivation()}
			<woltlab-core-notice type="warning">{lang}wcf.user.register.needAdminActivation{/lang}</woltlab-core-notice>
		{elseif !$__wcf->user->isEmailConfirmed()}
			<woltlab-core-notice type="warning">{lang}wcf.user.register.needEmailConfirmation{/lang}</woltlab-core-notice>
		{/if}
	{/if}
	
	{hascontent}
		{content}
			{foreach from=$__wcf->getNoticeHandler()->getVisibleNotices() item='notice'}
				{if $notice->isCustom()}
					<div class="{$notice->cssClassName} notice{if $notice->isDismissible} noticeDismissible active{/if}" role="status">
						{if $notice->isDismissible}
							<button type="button" class="jsDismissNoticeButton jsTooltip" data-object-id="{$notice->noticeID}" title="{lang}wcf.notice.button.dismiss{/lang}">
								{icon name='xmark'}
							</button>
						{/if}
						
						{@$notice}
					</div>
				{else}
					<woltlab-core-notice type="{$notice->cssClassName}" class="notice{if $notice->isDismissible} noticeDismissible active{/if}">
						{if $notice->isDismissible}
							<button type="button" class="jsDismissNoticeButton jsTooltip" data-object-id="{$notice->noticeID}" title="{lang}wcf.notice.button.dismiss{/lang}">
								{icon name='xmark'}
							</button>
						{/if}
						
						{@$notice}
					</woltlab-core-notice>
				{/if}
			{/foreach}
		{/content}
		
		<script data-relocate="true">
			require(['WoltLabSuite/Core/Controller/Notice/Dismiss'], function(ControllerNoticeDismiss) {
				ControllerNoticeDismiss.setup();
			});
		</script>
	{/hascontent}
	
	{event name='userNotice'}
{/capture}

{if $__userNotice|trim}
	<div class="userNotice">
		{@$__userNotice}
	</div>
{/if}

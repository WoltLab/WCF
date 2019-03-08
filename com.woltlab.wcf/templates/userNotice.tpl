{capture assign='__userNotice'}
	{if OFFLINE && $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
		<div class="warning">
			<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
			<div>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|language}{else}{@OFFLINE_MESSAGE|language|newlineToBreak}{/if}</div>
		</div>
	{/if}
	
	{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage') && $__wcf->getAvailableUpdates() && SHOW_UPDATE_NOTICE_FRONTEND}
		<p class="info">{lang}wcf.page.availableUpdates{/lang}</p>
	{/if}
	
	{if $__wcf->user->activationCode && REGISTER_ACTIVATION_METHOD == 1 && $templateName != 'registerActivation' && $templateName != 'register' && $templateName != 'redirect' && $__wcf->user->getBlacklistMatches()|empty}
		<p class="warning">{lang}wcf.user.register.needActivation{/lang}</p>
	{/if}
	
	{hascontent}
		{content}
			{foreach from=$__wcf->getNoticeHandler()->getVisibleNotices() item='notice'}
				<div class="{$notice->cssClassName} notice{if $notice->isDismissible} noticeDismissible active{/if}">
					{if $notice->isDismissible}
						<span class="icon icon16 fa-times pointer jsDismissNoticeButton jsTooltip" data-object-id="{$notice->noticeID}" title="{lang}wcf.notice.button.dismiss{/lang}"></span>
					{/if}
					
					{@$notice}
				</div>
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

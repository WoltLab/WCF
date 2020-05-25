{capture assign='__userNotice'}
	{if OFFLINE && $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
		<div class="warning" role="status">
			<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
			<div>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|language}{else}{@OFFLINE_MESSAGE|language|newlineToBreak}{/if}</div>
		</div>
	{/if}
	
	{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage') && $__wcf->getAvailableUpdates() && SHOW_UPDATE_NOTICE_FRONTEND}
		<p class="info" role="status">{lang}wcf.page.availableUpdates{/lang}</p>
	{/if}
	
	{* user needs email confirmation to be activated *}
	{if !$__wcf->user->isEmailConfirmed() && !$__wcf->user->isActivated() && REGISTER_ACTIVATION_METHOD & 1 && $templateName != 'registerActivation' && $templateName != 'register' && $templateName != 'redirect' && $__wcf->user->getBlacklistMatches()|empty}
		<p class="warning" role="status">{lang}wcf.user.register.needActivation{/lang}</p>
	{/if}
	
	{* user needs admin activation *}
	{if $__wcf->user->isEmailConfirmed() && REGISTER_ACTIVATION_METHOD & 2 && !$__wcf->user->isActivated() && $templateName != 'registerActivation' && $templateName != 'register' && $templateName != 'redirect' && $__wcf->user->getBlacklistMatches()|empty}
		<p class="warning" role="status">{lang}wcf.user.register.needAdminActivation{/lang}</p>
	{/if}
	
	{* user needs email activation w/o being disabled *}
	{if !$__wcf->user->isEmailConfirmed() && REGISTER_ACTIVATION_METHOD & 1 && $__wcf->user->isActivated() && $templateName != 'registerActivation' && $templateName != 'register' && $templateName != 'redirect' && $__wcf->user->getBlacklistMatches()|empty}
		<p class="warning" role="status">{lang}wcf.user.register.needEmailConfirmation{/lang}</p>
	{/if}
	
	{hascontent}
		{content}
			{foreach from=$__wcf->getNoticeHandler()->getVisibleNotices() item='notice'}
				<div class="{$notice->cssClassName} notice{if $notice->isDismissible} noticeDismissible active{/if}" role="status">
					{if $notice->isDismissible}
						<span role="button" tabindex="0" class="icon icon16 fa-times pointer jsDismissNoticeButton jsTooltip" data-object-id="{$notice->noticeID}" title="{lang}wcf.notice.button.dismiss{/lang}"></span>
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

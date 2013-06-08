<div class="userNotice">
	{if OFFLINE && $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
		<div class="warning">
			<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
			<p>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE|language}{else}{@OFFLINE_MESSAGE|language|htmlspecialchars|nl2br}{/if}</p>
		</div>
	{/if}
	
	{if $__wcf->user->activationCode && REGISTER_ACTIVATION_METHOD == 1}
		<p class="warning">{lang}wcf.user.register.needActivation{/lang}</p>
	{/if}
	
	{event name='userNotice'}
</div>
<div class="userNotice">
	{if OFFLINE && $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
		<div class="warning">
			<p><strong>{lang}wcf.page.offline{/lang}</strong></p>
			<p>{if OFFLINE_MESSAGE_ALLOW_HTML}{@OFFLINE_MESSAGE}{else}{@OFFLINE_MESSAGE|htmlspecialchars|nl2br}{/if}</p>
		</div>
	{/if}
	
	{event name='userNotice'}
</div>
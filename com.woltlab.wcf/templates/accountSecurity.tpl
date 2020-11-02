{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.user.security.activeSessions{/lang}</h2>
	
	<ul class="containerList">
		{foreach from=$activeSessions item=session}
			<li class="box64 sessionItem">
				<div>
					<span class="icon icon64 fa-{$session->getDeviceIcon()}"></span>
				</div>
				
				<div class="accountSecurityContainer">
					<div class="containerHeadline accountSecurityInformation">
						<h3>{$session->getUserAgent()->getBrowser()}</h3>
						<small>
							{$session->getIpAddress()}<br />
							{if $session->isCurrentSession()}
								{lang}wcf.user.security.currentSession{/lang}
							{else}
								{lang}wcf.user.security.sessionLastActive{/lang}
							{/if}
						</small>
					</div>
					
					{if !$session->isCurrentSession()}
						<div class="accountSecurityButtons">
							<button class="small sessionDeleteButton" data-session-id="{$session->getSessionID()}">{lang}wcf.user.security.deleteSession{/lang}</button>
						</div>
					{/if}
				</div>
			</li>
		{/foreach}
	</ul>
</section>

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/User/Session/Delete'], function(Language, UserSessionDelete) {
		Language.addObject({
			'wcf.user.security.deleteSession.confirmMessage': '{jslang}wcf.user.security.deleteSession.confirmMessage{/jslang}',
		});
		
		new (UserSessionDelete.default)();
	});
</script>

{include file='footer' __disableAds=true}

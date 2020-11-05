{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.user.security.multifactor{/lang}</h2>
	
	<ul class="containerList">
		{foreach from=$multifactorMethods item=method}
			<li class="box64">
				<div>
					<span class="icon icon64 fa-{if $method->icon}{$method->icon}{else}lock{/if}"></span>
				</div>
				
				<div class="accountSecurityContainer">
					<div class="containerHeadline accountSecurityInformation">
						<h3>
							{lang}wcf.user.security.multifactor.{$method->objectType}{/lang}
							
							{if $enabledMultifactorMethods[$method->objectTypeID]|isset}
								<span class="badge green">
									{lang}wcf.user.security.multifactor.active{/lang}
								</span>
							{/if}
						</h3>
						
						{if $enabledMultifactorMethods[$method->objectTypeID]|isset}
							{$method->getProcessor()->getStatusText($enabledMultifactorMethods[$method->objectTypeID])}
						{/if}
					</div>
					
					<div class="accountSecurityButtons">
						<a class="small button" href="{link controller='MultifactorManage' id=$method->objectTypeID}{/link}">
							{lang}wcf.user.security.multifactor.{if $enabledMultifactorMethods[$method->objectTypeID]|isset}manage{else}setup{/if}{/lang}
						</a>
					</div>
				</div>
			</li>
		{/foreach}
	</ul>
</section>

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
						<h3>{lang}wcf.user.security.sessionName{/lang}</h3>
						
						<dl class="plain inlineDataList small">
							<dt>{lang}wcf.user.security.lastActivity{/lang}</dt>
							<dd>{if $session->isCurrentSession()}{lang}wcf.user.security.currentSession{/lang}{else}{@$session->getLastActivityTime()|time}{/if}</dd>
							
							<dt>{lang}wcf.user.security.ipAddress{/lang}</dt>
							<dd>{$session->getIpAddress()}</dd>
						</dl>
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

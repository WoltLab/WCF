{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{if $requiresMultifactor}
	<woltlab-core-notice type="warning">{lang}wcf.user.security.requiresMultifactor{/lang}</woltlab-core-notice>
{/if}

<section class="section" id="section_multifactor">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.user.security.multifactor{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.user.security.multifactor.description{/lang}</p>
	</header>
	
	<ul class="containerList">
		{foreach from=$multifactorMethods item=method}
			{if $method->objectType !== 'com.woltlab.wcf.multifactor.backup' || $enabledMultifactorMethods[$method->objectTypeID]|isset}
				<li class="box64">
					<div class="accountSecurityInformationIcon">
						{if $method->icon}
							{icon size=64 name=$method->icon}
						{else}
							{icon size=64 name='lock'}
						{/if}
					</div>
					
					<div class="accountSecurityContainer">
						<div class="containerHeadline accountSecurityInformation">
							<h3>
								<a href="{link controller='MultifactorManage' object=$method}{/link}" class="accountSecurityInformationLink">
									{lang}wcf.user.security.multifactor.{$method->objectType}{/lang}
								</a>
								
								{if $enabledMultifactorMethods[$method->objectTypeID]|isset}
									<span class="badge green">
										{lang}wcf.user.security.multifactor.active{/lang}
									</span>
								{/if}
							</h3>
							
							{if $enabledMultifactorMethods[$method->objectTypeID]|isset}
								{@$method->getProcessor()->getStatusText($enabledMultifactorMethods[$method->objectTypeID])}
							{else}
								{lang}wcf.user.security.multifactor.{$method->objectType}.description{/lang}
							{/if}
						</div>
						
						<div class="accountSecurityButtons">
							{if $enabledMultifactorMethods[$method->objectTypeID]|isset}
								{if $method->objectType !== 'com.woltlab.wcf.multifactor.backup'}
									<a class="small button" href="{link controller='MultifactorDisable' object=$enabledMultifactorMethods[$method->objectTypeID]}{/link}">
										{lang}wcf.user.security.multifactor.disable{/lang}
									</a>
								{/if}
								
								<a class="small button buttonPrimary" href="{link controller='MultifactorManage' object=$method}{/link}">
									{lang}wcf.user.security.multifactor.manage{/lang}
								</a>
							{else}
								<a class="small button buttonPrimary" href="{link controller='MultifactorManage' object=$method}{/link}">
									{lang}wcf.user.security.multifactor.setup{/lang}
								</a>
							{/if}
						</div>
					</div>
				</li>
			{/if}
		{/foreach}
	</ul>
</section>

<section class="section" id="section_activeSessions">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.user.security.activeSessions{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.user.security.activeSessions.description{/lang}</p>
	</header>
	
	<ul class="containerList">
		{foreach from=$activeSessions item=session}
			<li class="box64 sessionItem">
				<div>
					{icon size=64 name=$session->getUserAgent()->getDeviceIcon()}
				</div>
				
				<div class="accountSecurityContainer">
					<div class="containerHeadline accountSecurityInformation">
						<h3 title="{$session->getUserAgent()}">{lang}wcf.user.security.sessionName{/lang}</h3>
						
						<dl class="plain inlineDataList small">
							<dt>{lang}wcf.user.security.lastActivity{/lang}</dt>
							<dd>{if $session->isCurrentSession()}{lang}wcf.user.security.currentSession{/lang}{else}{@$session->getLastActivityTime()|time}{/if}</dd>
							
							<dt>{lang}wcf.user.security.ipAddress{/lang}</dt>
							<dd title="{$session->getIpAddress()}">{$session->getIpAddress()->toBulletMasked(16, 48)}</dd>
						</dl>
					</div>
					
					{if !$session->isCurrentSession()}
						<div class="accountSecurityButtons">
							<button type="button" class="button small sessionDeleteButton" data-session-id="{$session->getSessionID()}">{lang}wcf.user.security.deleteSession{/lang}</button>
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

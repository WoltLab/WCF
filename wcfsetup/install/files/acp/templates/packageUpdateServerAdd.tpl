{include file='header' pageTitle='wcf.acp.updateServer.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageUpdateServerList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<woltlab-core-notice type="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br>{$packageUpdateServer->errorMessage}</woltlab-core-notice>
{/if}

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='PackageUpdateServerAdd'}{/link}{else}{link controller='PackageUpdateServerEdit' id=$packageUpdateServerID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'serverURL'} class="formError"{/if}>
			<dt><label for="serverURL">{lang}wcf.acp.updateServer.serverURL{/lang}</label></dt>
			<dd>
				<input type="url" id="serverURL" name="serverURL" value="{$serverURL}" required autofocus class="long"{if $action != 'add'} readonly{/if}>
				{if $errorField == 'serverURL'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType[duplicate]|isset}
							{lang}wcf.acp.updateServer.serverURL.error.duplicate{/lang}
						{else}
							{lang}wcf.acp.updateServer.serverURL.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="loginUsername">{lang}wcf.acp.updateServer.loginUsername{/lang}</label></dt>
			<dd>
				<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="medium">
				<small>{lang}wcf.acp.updateServer.loginUsername.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="loginPassword">{lang}wcf.acp.updateServer.loginPassword{/lang}</label></dt>
			<dd>
				<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="medium" autocomplete="off"{if $action != 'add' && $loginUsername} placeholder="{lang}wcf.acp.updateServer.loginPassword.noChange{/lang}"{/if}>
				<small>{lang}wcf.acp.updateServer.loginPassword.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}

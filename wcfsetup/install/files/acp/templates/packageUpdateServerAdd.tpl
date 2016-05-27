{include file='header' pageTitle='wcf.acp.updateServer.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageUpdateServerList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br />{$packageUpdateServer->errorMessage}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='PackageUpdateServerAdd'}{/link}{else}{link controller='PackageUpdateServerEdit' id=$packageUpdateServerID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'serverURL'} class="formError"{/if}>
			<dt><label for="serverURL">{lang}wcf.acp.updateServer.serverURL{/lang}</label></dt>
			<dd>
				<input type="url" id="serverURL" name="serverURL" value="{$serverURL}" required="required" autofocus="autofocus" class="long" />
				{if $errorField == 'serverURL'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
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
				<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="medium" />
				<small>{lang}wcf.acp.updateServer.loginUsername.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt><label for="loginPassword">{lang}wcf.acp.updateServer.loginPassword{/lang}</label></dt>
			<dd>
				<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="medium" autocomplete="off" />
				<small>{lang}wcf.acp.updateServer.loginPassword.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

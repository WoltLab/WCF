{include file='header'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br />{$packageUpdateServer->errorMessage}</p>	
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UpdateServerList'}{/link}" title="{lang}wcf.acp.menu.link.package.server.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/server1.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UpdateServerAdd'}{/link}{else}{link controller='UpdateServerEdit'}{/link}{/if}">
	<div class="container containerPadding marginTop shadow">
		<fieldset>
			<legend>{lang}wcf.acp.updateServer.data{/lang}</legend>
			
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
					<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="medium" />
					<small><p>{lang}wcf.acp.updateServer.loginPassword.description{/lang}</p></small>
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{if $packageUpdateServerID|isset}<input type="hidden" name="id" value="{@$packageUpdateServerID}" />{/if}
	</div>
</form>

{include file='footer'}

{include file='header'}

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/{@$action}1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<p class="wcf-warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br />{$packageUpdateServer->errorMessage}</p>	
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='UpdateServerList'}{/link}" title="{lang}wcf.acp.menu.link.package.server.list{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/server1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UpdateServerAdd'}{/link}{else}{link controller='UpdateServerEdit'}{/link}{/if}">
	<div class="wcf-border wcf-content">
		
		<fieldset>
			<legend>{lang}wcf.acp.updateServer.data{/lang}</legend>
			
			<dl{if $errorField == 'serverURL'} class="wcf-formError"{/if}>
				<dt><label for="serverURL">{lang}wcf.acp.updateServer.serverURL{/lang}</label></dt>
				<dd>
					<input type="url" id="serverURL" name="serverURL" value="{$serverURL}" required="required" autofocus="autofocus" class="long" />
					{if $errorField == 'serverURL'}
						<small class="wcf-innerError">
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
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{if $packageUpdateServerID|isset}<input type="hidden" name="id" value="{@$packageUpdateServerID}" />{/if}
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}

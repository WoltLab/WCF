{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{@$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.form.{$action}.success{/lang}</p>	
{/if}

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br />{$packageUpdateServer->errorMessage}</p>	
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul><li><a href="index.php?page=UpdateServerList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.server.list{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/server1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li></ul>
	</nav>
</div>

<form method="post" action="index.php?form=UpdateServer{@$action|ucfirst}{if $packageUpdateServerID|isset}&amp;packageUpdateServerID={@$packageUpdateServerID}{/if}">
	<div class="border content">
		
		<fieldset>
			<legend>{lang}wcf.acp.updateServer.data{/lang}</legend>
			
			<dl id="serverURLDiv"{if $errorField == 'serverURL'} class="formError"{/if}>
				<dt><label for="serverURL">{lang}wcf.acp.updateServer.serverURL{/lang}</label></dt>
				<dd>
					<input type="text" id="serverURL" name="serverURL" value="{$serverURL}" class="long" />
					{if $errorField == 'serverURL'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'notValid'}{lang}wcf.acp.updateServer.serverURL.error.notValid{/lang}{/if}
						</small>
					{/if}
					<small id="serverURLHelpMessage">{lang}wcf.acp.updateServer.serverURL.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="loginUsernameDiv">
				<dt><label for="loginUsername">{lang}wcf.acp.updateServer.loginUsername{/lang}</label></dt>
				<dd>
					<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="long" />
					<small id="loginUsernameHelpMessage">{lang}wcf.acp.updateServer.loginUsername.description{/lang}</small>
				</dd>
			</dl>
			
			<dl id="loginPasswordDiv">
				<dt><label for="loginPassword">{lang}wcf.acp.updateServer.loginPassword{/lang}</label></dt>
				<dd>
					<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="long" />
					<small id="loginPasswordHelpMessage"><p>{lang}wcf.acp.updateServer.loginPassword.description{/lang}</p></small>
				</dd>
			</dl>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</fieldset>
		
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}

{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/updateServer{@$action|ucfirst}L.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.updateServer.{$action}.success{/lang}</p>	
{/if}

{if $packageUpdateServer|isset && $packageUpdateServer->errorMessage}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br />{$packageUpdateServer->errorMessage}</p>	
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul><li><a href="index.php?page=UpdateServerList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.server.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerM.png" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.view{/lang}</span></a></li></ul>
	</nav>
</div>
<form method="post" action="index.php?form=UpdateServer{@$action|ucfirst}{if $packageUpdateServerID|isset}&amp;packageUpdateServerID={@$packageUpdateServerID}{/if}">
	<div class="border content">
		<div class="container-1">
	
			<fieldset>
				<legend>{lang}wcf.acp.updateServer.data{/lang}</legend>
				
				<div id="serverURLDiv" class="formElement{if $errorField == 'serverURL'} formError{/if}">
					<div class="formFieldLabel">
						<label for="serverURL">{lang}wcf.acp.updateServer.serverURL{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="serverURL" name="serverURL" value="{$serverURL}" class="inputText" />
						{if $errorField == 'serverURL'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.acp.updateServer.serverURL.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div id="serverURLHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.updateServer.serverURL.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('serverURL');
				//]]></script>
				
				<div id="loginUsernameDiv" class="formElement">
					<div class="formFieldLabel">
						<label for="loginUsername">{lang}wcf.acp.updateServer.loginUsername{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="inputText" />
					</div>
					<div id="loginUsernameHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.updateServer.loginUsername.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('loginUsername');
				//]]></script>
				
				<div id="loginPasswordDiv" class="formElement">
					<div class="formFieldLabel">
						<label for="loginPassword">{lang}wcf.acp.updateServer.loginPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="inputText" />
					</div>
					<div id="loginPasswordHelpMessage" class="formFieldDesc hidden">
						<p>{lang}wcf.acp.updateServer.loginPassword.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('loginPassword');
				//]]></script>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}

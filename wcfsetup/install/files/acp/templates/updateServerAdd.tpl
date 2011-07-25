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
				
				<div class="formElement{if $errorField == 'server'} formError{/if}" id="serverDiv">
					<div class="formFieldLabel">
						<label for="server">{lang}wcf.acp.updateServer.server{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="server" value="{$server}" id="server" />
						{if $errorField == 'server'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.acp.updateServer.server.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="serverHelpMessage">
						<p>{lang}wcf.acp.updateServer.server.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('server');
				//]]></script>
				
				<div class="formElement" id="loginUsernameDiv">
					<div class="formFieldLabel">
						<label for="loginUsername">{lang}wcf.acp.updateServer.loginUsername{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="loginUsername" value="{$loginUsername}" id="loginUsername" />
					</div>
					<div class="formFieldDesc hidden" id="loginUsernameHelpMessage">
						<p>{lang}wcf.acp.updateServer.loginUsername.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('loginUsername');
				//]]></script>
				
				<div class="formElement" id="loginPasswordDiv">
					<div class="formFieldLabel">
						<label for="loginPassword">{lang}wcf.acp.updateServer.loginPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="loginPassword" value="{$loginPassword}" id="loginPassword" />
					</div>
					<div class="formFieldDesc hidden" id="loginPasswordHelpMessage">
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
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}

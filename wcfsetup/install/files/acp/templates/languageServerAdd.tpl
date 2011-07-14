{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageServer{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.languageServer.{$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.languageServer.{$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=LanguageServerList{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.server.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/languageServerM.png" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=LanguageServer{@$action|ucfirst}{if $languageServerID|isset}&amp;languageServerID={@$languageServerID}{/if}">
	<div class="border content">
		<div class="container-1">
	
			<fieldset>
				<legend>{lang}wcf.acp.languageServer.data{/lang}</legend>
				
				<div class="formElement{if $errorField == 'server'} formError{/if}" id="serverDiv">
					<div class="formFieldLabel">
						<label for="server">{lang}wcf.acp.languageServer.server{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="server" value="{$server}" id="server" />
						{if $errorField == 'server'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.acp.languageServer.server.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="serverHelpMessage">
						<p>{lang}wcf.acp.languageServer.server.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('server');
				//]]></script>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}

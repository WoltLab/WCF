{include file='header'}

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/{$action}1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.languageServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="wcf-success">{lang}wcf.acp.languageServer.{$action}.success{/lang}</p>	
{/if}

<div class="wcf-contentHeader">
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='LanguageServerList'}{/link}" title="{lang}wcf.acp.menu.link.package.server.view{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.view{/lang}</span></a></li>
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='LanguageServerAdd'}{/link}{else}{link controller='LanguageServerEdit' id=$languageServerID}{/link}{/if}">
	<div class="wcf-border wcf-content">
		<fieldset>
			<legend>{lang}wcf.acp.languageServer.data{/lang}</legend>
			
			<dl{if $errorField == 'server'} class="wcf-formError"{/if}>
				<dt><label for="server">{lang}wcf.acp.languageServer.server{/lang}</label></dt>
				<dd>
					<input type="text" name="server" id="server" value="{$server}" class="long" />
					{if $errorField == 'server'}
						<small class="wcf-innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType == 'notValid'}{lang}wcf.acp.languageServer.server.error.notValid{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.languageServer.server.description{/lang}</small>
				</dd>
			</dl>
		
		</fieldset>
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}

{include file='header' pageTitle='wcf.acp.languageServer.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.languageServer.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageServerList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.language.server.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.acp.languageServer.{$action}.success{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='LanguageServerAdd'}{/link}{else}{link controller='LanguageServerEdit' id=$languageServerID}{/link}{/if}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.languageServer.data{/lang}</h2>
		
		<dl{if $errorField == 'server'} class="formError"{/if}>
			<dt><label for="server">{lang}wcf.acp.languageServer.server{/lang}</label></dt>
			<dd>
				<input type="text" name="server" id="server" value="{$server}" autofocus class="long">
				{if $errorField == 'server'}
					<small class="innerError">
						{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
						{if $errorType == 'notValid'}{lang}wcf.acp.languageServer.server.error.notValid{/lang}{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.languageServer.server.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

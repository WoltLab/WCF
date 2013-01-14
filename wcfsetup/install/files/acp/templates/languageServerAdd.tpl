{include file='header' pageTitle='wcf.acp.languageServer.'|concat:$action}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.languageServer.{$action}{/lang}</h1>
	</hgroup>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.languageServer.{$action}.success{/lang}</p>	
{/if}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.language.canDeleteServer') || $__wcf->session->getPermission('admin.language.canEditServer')}
						<li><a href="{link controller='LanguageServerList'}{/link}" title="{lang}wcf.acp.menu.link.language.server.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.menu.link.language.server.list{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

<form method="post" action="{if $action == 'add'}{link controller='LanguageServerAdd'}{/link}{else}{link controller='LanguageServerEdit' id=$languageServerID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.acp.languageServer.data{/lang}</legend>
			
			<dl{if $errorField == 'server'} class="formError"{/if}>
				<dt><label for="server">{lang}wcf.acp.languageServer.server{/lang}</label></dt>
				<dd>
					<input type="text" name="server" id="server" value="{$server}" autofocus="autofocus" class="long" />
					{if $errorField == 'server'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType == 'notValid'}{lang}wcf.acp.languageServer.server.error.notValid{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.languageServer.server.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
 	</div>
</form>

{include file='footer'}

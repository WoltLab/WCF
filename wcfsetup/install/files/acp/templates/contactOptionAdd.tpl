{include file='header' pageTitle='wcf.acp.contact.option.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.contact.option.{@$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ContactSettings'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.contact.settings{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action === 'add'}{link controller='ContactOptionAdd'}{/link}{else}{link controller='ContactOptionEdit' id=$optionID}{/link}{/if}">
	{include file='customOptionAdd'}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}

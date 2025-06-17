{include file='header' pageTitle='wcf.acp.contact.recipient.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.contact.recipient.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ContactRecipientList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.contact.recipient.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$form->getHtml()}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.updateServer.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageUpdateServerList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.package.server.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $formObject|isset && $formObject->errorMessage}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br>{$formObject->errorMessage}</p>
{/if}

{@$form->getHtml()}

{include file='footer'}

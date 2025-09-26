{include file='header' pageTitle="wcf.acp.updateServer.$action"}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.updateServer.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				<li>
					{unsafe:$interactionContextMenu->render()}
				</li>
			{/if}
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $formObject|isset && $formObject->errorMessage}
	<woltlab-core-notice type="warning">{lang}wcf.acp.updateServer.lastErrorMessage{/lang}<br>{$formObject->errorMessage}</woltlab-core-notice>
{/if}

{unsafe:$form->getHtml()}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.template.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.template.{$action}{/lang}{if $action == 'edit'}: {$formObject->templateName}{/if}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$formObject->getPath()}</p>{/if}
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

{if $hasTemplateGroups}
	{unsafe:$form->getHtml()}
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.template.error.noGroups{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

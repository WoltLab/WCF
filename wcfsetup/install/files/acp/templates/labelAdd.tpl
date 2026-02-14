{include file='header' pageTitle='wcf.acp.label.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.{$action}{/lang}</h1>
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

{if $hasLabelGroups}
	{unsafe:$form->getHtml()}
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.label.error.noGroups{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.label.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.{$action}{/lang}{if $action == 'edit'}: {$formObject->getTitle()}{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $action == 'edit'}
						<li>
							{unsafe:$interactionContextMenu->render()}
						</li>
					{/if}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{if $hasLabelGroups}
	{unsafe:$form->getHtml()}
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.label.error.noGroups{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

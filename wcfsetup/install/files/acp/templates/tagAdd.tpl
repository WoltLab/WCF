{include file='header' pageTitle='wcf.acp.tag.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.tag.{$action}{/lang}{if $action == 'edit'}: {$formObject->getTitle()}{/if}</h1>
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

{unsafe:$form->getHtml()}

{include file='footer'}

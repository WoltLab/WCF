{include file='header' pageTitle='wcf.acp.bbcode.mediaProvider.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.bbcode.mediaProvider.{$action}{/lang}{if $action == 'edit'}: {$formObject->getTitle()}{/if}</h1>
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

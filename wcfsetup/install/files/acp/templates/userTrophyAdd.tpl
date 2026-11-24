{include file='header' pageTitle='wcf.acp.menu.link.userTrophy.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.userTrophy.{$action}{/lang}{if $action == 'edit'}: {$formObject->getTrophy()->getTitle()}{/if}</h1>
		{if $action == 'edit'}<p class="contentHeaderDescription">{$formObject->getUserProfile()->getUsername()}</p>{/if}
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $action === 'edit'}
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

{unsafe:$form->getHTML()}

{include file='footer'}

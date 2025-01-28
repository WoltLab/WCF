{include file='header' pageTitle='wcf.acp.user.rank.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.rank.{$action}{/lang}</h1>
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

{unsafe:$form->getHtml()}

{include file='footer'}

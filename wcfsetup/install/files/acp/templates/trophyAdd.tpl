{include file='header' pageTitle='wcf.acp.menu.link.trophy.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.trophy.{$action}{/lang}</h1>
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

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Icon/Badge'], ({ IconBadge }) => {
		new IconBadge('iconName', 'iconColor', 'badgeColor');
	});
</script>

{include file='footer'}

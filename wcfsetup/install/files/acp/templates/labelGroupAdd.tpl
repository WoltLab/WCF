{include file='header' pageTitle='wcf.acp.label.group.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.group.{$action}{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				{if !$formObject->sortAlphabetically}
					<li><button type="button" class="button jsChangeShowOrder">{icon name='up-down'} <span>{lang}wcf.global.changeShowOrder{/lang}</span></button></li>
				{/if}
				<li>
					{unsafe:$interactionContextMenu->render()}
				</li>
			{/if}

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{unsafe:$form->getHtml()}

{if $action == 'edit' && !$formObject->sortAlphabetically}
	<script data-relocate="true">
		require(["WoltLabSuite/Core/Component/ChangeShowOrder"], ({ setup }) => {
			{jsphrase name='wcf.global.changeShowOrder'}

			setup(
				document.querySelector('.jsChangeShowOrder'),
				'core/labels/groups/{$formObject->groupID}/labels/show-order'
			);
		});
	</script>
{/if}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.label.group.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.group.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $gridView->countRows() > 1}
				<li><button type="button" class="button jsChangeShowOrder">{icon name='up-down'} <span>{lang}wcf.global.changeShowOrder{/lang}</span></button></li>
			{/if}
			<li><a href="{link controller='LabelGroupAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.label.group.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{if $gridView->countRows() > 1}
	<script data-relocate="true">
		require(["WoltLabSuite/Core/Component/ChangeShowOrder"], ({ setup }) => {
			{jsphrase name='wcf.global.changeShowOrder'}
			
			setup(
				document.querySelector('.jsChangeShowOrder'),
				'core/labels/groups/show-order'
			);
		});
	</script>
{/if}

{include file='footer'}

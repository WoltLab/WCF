{include file='header' pageTitle='wcf.acp.ad.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.ad.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $gridView->countRows() > 1}
				<li>
					<button type="button" class="button jsChangeShowOrder">{icon name='up-down'} <span>{lang}wcf.global.changeShowOrder{/lang}</span></button>
				</li>
			{/if}
			<li><a href="{link controller='AdAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.menu.link.ad.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Component/ChangeShowOrder"], ({ setup }) => {
		{jsphrase name='wcf.global.changeShowOrder'}

		setup(
			document.querySelector('.jsChangeShowOrder'),
			'core/ads/show-order',
		);
	});
</script>

{include file='footer'}

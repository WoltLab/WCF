{include file='header' pageTitle='wcf.acp.smiley.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.smiley.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li>
				<button type="button" class="button jsChangeShowOrder">{icon name='up-down'} <span>{lang}wcf.global.changeShowOrder{/lang}</span></button>
			</li>
			<li><a href="{link controller='SmileyAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.smiley.add{/lang}</span></a></li>
			
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
			'core/smilies/show-order',
		);
	});
</script>

{include file='footer'}

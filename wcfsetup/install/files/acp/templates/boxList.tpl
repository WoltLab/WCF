{include file='header' pageTitle='wcf.acp.box.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.box.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li>
				<button type="button" class="button jsButtonBoxAdd">
					{icon name='plus'}
					<span>{lang}wcf.acp.box.add{/lang}</span>
				</button>
			</li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='boxAddDialog'}

{include file='footer'}

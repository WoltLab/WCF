{include file='header' pageTitle='wcf.acp.page.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.page.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li>
				<button class="button jsButtonPageAdd">
					{icon name='plus'}
					<span>{lang}wcf.acp.page.add{/lang}</span>
				</button>
			</li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='pageAddDialog'}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.menu.item.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.item.list{/lang}</h1>
		<p class="contentHeaderDescription">{$menu->getTitle()}</p>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='MenuEdit' id=$menuID}{/link}" class="button">{icon name='pencil'} <span>{lang}wcf.acp.menu.edit{/lang}</span></a></li>
			<li><a href="{link controller='MenuItemAdd' menuID=$menuID}{/link}" class="button buttonPrimary">{icon name='plus'} <span>{lang}wcf.acp.menu.item.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<div class="section">
	{unsafe:$nodeTreeView->render()}
</div>

{include file='footer'}

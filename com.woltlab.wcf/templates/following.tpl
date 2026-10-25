{include file='userMenuSidebar'}

{include file='header' __sidebarLeftHasMenu=true}

<div class="section {$listView->getContainerCssClassName()}">
	{unsafe:$listView->render()}
</div>

{include file='footer'}

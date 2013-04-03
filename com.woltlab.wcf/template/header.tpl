<a id="top"></a>

<header id="pageHeader" class="layoutFluid">
	<div>
		<nav id="topMenu" class="userPanel">
			<div class="layoutFluid clearfix">
				{hascontent}
					<ul class="userPanelItems">
						{content}{event name='topMenu'}{/content}
					</ul>
				{/hascontent}
				
				{event name='searchArea'}
			</div>
		</nav>
		
		<div id="logo" class="logo">
			<a href="{link}{/link}">
				<img src="{@$__wcf->getPath('wbb')}images/wbbLogo2.svg" alt="" style="height: 90px; width: 246px;" />
				{event name='headerLogo'}
			</a>
		</div>
		
		{event name='headerContents'}
		
		{include file='mainMenu'}
		
		<nav class="navigation navigationHeader clearfix">
			{include file='mainMenuSubMenu'}
			
			<ul class="navigationIcons">
				<li id="toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-down"></span> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				<li id="sitemap" class="jsOnly"><a title="{lang}wcf.page.sitemap{/lang}" class="jsTooltip"><span class="icon icon16 icon-sitemap"></span> <span class="invisible">{lang}wcf.page.sitemap{/lang}</span></a></li>
				{if $headerNavigation|isset}{@$headerNavigation}{/if}
				{event name='navigationIcons'}
			</ul>
		</nav>
	</div>
</header>

<div id="main" class="layoutFluid{if $sidebarOrientation|isset && $sidebar|isset} sidebarOrientation{@$sidebarOrientation|ucfirst} clearfix{if $sidebarOrientation == 'right' && $sidebarCollapsed} sidebarCollapsed{/if}{/if}">
	<div>
		{if $sidebar|isset}
			<aside class="sidebar"{if $sidebarOrientation|isset && $sidebarOrientation == 'right'} data-is-open="{if $sidebarCollapsed}false{else}true{/if}" data-sidebar-name="{$sidebarName}"{/if}>
				<div>
					{event name='sidebarBoxesTop'}
					
					{@$sidebar}
					
					{event name='sidebarBoxesBottom'}
				</div>
			</aside>
			
			{if $sidebarOrientation|isset && $sidebarOrientation == 'right'}
				<script type="text/javascript">
					//<![CDATA[
					$(function() {
						new WCF.Collapsible.Sidebar();
					});
					//]]>
				</script>
			{/if}
		{/if}
				
		<section id="content" class="content clearfix">
			
			{event name='contents'}
			
			{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
			
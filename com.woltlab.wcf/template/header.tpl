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
			<a href="{link controller='Index'}{/link}">
				<img src="{@$__wcf->getPath('wbb')}images/wbbLogo2.svg" alt="" style="height: 90px; width: 246px;" />
				{*event name='headerLogo'*}
			</a>
		</div>
		
		{include file='mainMenu'}
		
		<nav class="navigation navigationHeader clearfix">
			{include file='mainMenuSubMenu'}
			
			<ul class="navigationIcons">
				<li id="toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{icon}circleArrowDownColored{/icon}" alt="" class="icon16" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				<li id="sitemap"><a title="{lang}wcf.sitemap.title{/lang}" class="jsTooltip"><img src="{icon}switchColored{/icon}" alt="" class="icon16" /> <span class="invisible">{lang}wcf.sitemap.title{/lang}</span></a></li>
				{if $headerNavigation|isset}{@$headerNavigation}{/if}
				{event name='headerNavigation'}
			</ul>
		</nav>
	</div>
</header>

<div id="main" class="layoutFluid{if $sidebarOrientation|isset && $sidebar|isset} sidebarOrientation{@$sidebarOrientation|ucfirst} clearfix{/if}">
	<div>
		{if $sidebar|isset}
			<aside class="sidebar"{if $sidebarOrientation|isset && $sidebarOrientation == 'right'} data-is-open="{if $sidebarCollapsed}false{else}true{/if}" data-sidebar-name="{$sidebarName}"{/if}>
				{@$sidebar}
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
			
			{if $skipBreadcrumbs|empty}{include file='breadcrumbs'}{/if}
			
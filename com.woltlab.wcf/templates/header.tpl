<a id="top"></a>

<div id="pageContainer" class="pageContainer">
	{event name='beforePageHeader'}
	
	{include file='pageHeader'}
	
	{event name='afterPageHeader'}
	
	{include file='pageNavbarTop'}
	
	<section id="main" class="main" role="main">
		<div class="{if $__wcf->getStyleHandler()->getStyle()->getVariable('useFluidLayout')}layoutFluid{else}layoutFixed{/if}">
			{capture assign='__sidebar'}
				{if $sidebar|isset}
					<aside class="sidebar"{if $sidebarOrientation|isset && $sidebarOrientation == 'right'} data-is-open="{if $sidebarCollapsed}false{else}true{/if}" data-sidebar-name="{$sidebarName}"{/if}>
						{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}{/if}
						
						{event name='sidebarBoxesTop'}
						
						{@$sidebar}
						
						{event name='sidebarBoxesBottom'}
						
						{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}{/if}
					</aside>
					
					{if $sidebarOrientation|isset && $sidebarOrientation == 'right'}
						<script data-relocate="true">
							require(['WoltLab/WCF/Ui/Collapsible/Sidebar'], function(UiCollapsibleSidebar) {
								UiCollapsibleSidebar.setup();
							});
						</script>
					{/if}
				{/if}
			{/capture}
			
			{if !$sidebarOrientation|isset || $sidebarOrientation == 'left'}
				{@$__sidebar}
			{/if}
			
			<div id="content" class="content">
				{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.header.content')}{/if}
				
				{event name='contents'}

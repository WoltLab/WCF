<a id="top"></a>

<div id="pageContainer" class="pageContainer">
	{event name='beforePageHeader'}
	
	{include file='pageHeader'}
	
	{event name='afterPageHeader'}
	
	{hascontent}
		<div class="boxesHeaderBoxes">
			<div class="layoutBoundary">
				{content}
					{foreach from=$__wcf->getBoxHandler()->getBoxes('headerBoxes') item=box}
						{@$box}
					{/foreach}
				{/content}
			</div>
		</div>
	{/hascontent}
	
	{include file='pageNavbarTop'}
	
	{hascontent}
		<div class="boxesTop">
			<div class="layoutBoundary">
				{content}
					{foreach from=$__wcf->getBoxHandler()->getBoxes('top') item=box}
						{@$box}
					{/foreach}
				{/content}
			</div>
		</div>
	{/hascontent}
	
	<section id="main" class="main" role="main">
		<div class="layoutBoundary">
			{hascontent}
				<aside class="sidebar boxesSidebarLeft">
					{content}
						{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}{/if}
						
						{event name='boxesSidebarLeftTop'}
						
						{* WCF2.1 Fallback *}
						{if !$sidebar|empty}
							{if !$sidebarOrientation|isset || $sidebarOrientation == 'left'}
								{@$sidebar}
							{/if}	
						{/if}
						
						{if !$sidebarLeft|empty}
							{@$sidebarLeft}
						{/if}
						
						{foreach from=$__wcf->getBoxHandler()->getBoxes('sidebarLeft') item=box}
							{@$box}
						{/foreach}
			
						{event name='boxesSidebarLeftBottom'}
			
						{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}{/if}
					{/content}
				</aside>
			{/hascontent}
			
			<div id="content" class="content">
				{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.header.content')}{/if}
				
				{hascontent}
					<div class="boxesContentTop">
						{content}
							{foreach from=$__wcf->getBoxHandler()->getBoxes('contentTop') item=box}
								{@$box}
							{/foreach}
						{/content}
					</div>
				{/hascontent}
				
				{event name='contents'}

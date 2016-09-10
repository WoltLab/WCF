<div id="pageHeaderContainer" class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div id="pageHeaderPanel" class="pageHeaderPanel">
			<div class="layoutBoundary">
				{include file='pageHeaderMenu'}
				
				{include file='pageHeaderUser'}
			</div>
		</div>
		
		<div id="pageHeaderFacade" class="pageHeaderFacade">
			<div class="layoutBoundary">
				{include file='pageHeaderLogo'}
				
				{include file='pageHeaderSearch'}
			</div>
		</div>
		
		{* TODO: this should be moved somewhere else and turned into an option *}
		<script data-relocate="true">
			require(['WoltLabSuite/Core/Ui/Page/Header/Fixed'], function(UiPageHeaderFixed) {
				UiPageHeaderFixed.init();
			});
		</script>
	</header>
	
	{hascontent}
		<div class="boxesHero">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{foreach from=$__wcf->getBoxHandler()->getBoxes('hero') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>	
			</div>
		</div>
	{/hascontent}
</div>

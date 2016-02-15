<div id="pageHeaderContainer" class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div>
			<div class="layoutBoundary">
				{include file='pageHeaderLogo'}
				
				{include file='pageHeaderUser'}
				
				{include file='pageHeaderMenu'}
				
				{include file='pageHeaderSearch'}
			</div>
		</div>
		
		<script data-relocate="true">
			require(['WoltLab/WCF/Ui/Page/Header/Fixed'], function(UiPageHeaderFixed) {
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

<div id="pageHeaderContainer" class="pageHeaderContainer">
	<header id="pageHeader" class="pageHeader">
		<div>
			<div class="layoutBoundary">
				<div class="pageHeaderContainerLeft">
					{include file='pageHeaderLogo'}
					
					{include file='pageHeaderMenu'}
				</div>
				<div class="pageHeaderContainerRight">
					{include file='pageHeaderSearch'}
					
					{include file='pageHeaderUser'}
				</div>
			</div>
		</div>
		
		{* TODO: this should be moved somewhere else and turned into an option *}
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

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
			</div>
		</div>
	</header>
	
	{hascontent}
		<div class="boxesHero">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{if !$boxesHero|empty}
							{@$boxesHero}
						{/if}

						{foreach from=$__wcf->getBoxHandler()->getBoxes('hero') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
</div>

{assign var='__pageHeaderLayout' value=$__wcf->getStyleHandler()->getStyle()->getVariable('pageHeaderLayout')}
<div id="pageHeaderContainer" class="pageHeaderContainer pageHeaderContainer--{$__pageHeaderLayout}">
	{if $__pageHeaderLayout === 'logoTop'}
		<div id="pageHeaderFacade" class="pageHeaderFacade">
			<div class="layoutBoundary">
				{include file='system_pageHeaderLogo'}
			</div>
		</div>
	{/if}
	
	<header id="pageHeader" class="pageHeader">
		<div id="pageHeaderPanel" class="pageHeaderPanel">
			<div class="layoutBoundary">
				{if $__pageHeaderLayout === 'logoInBar'}
					{include file='system_pageHeaderLogo'}
				{else}
					{* The logo row is hidden on small screens, the bar shows the mobile logo instead. *}
					<a href="{if PAGE_LOGO_LINK_TO_APP_DEFAULT}{link application=$__wcf->getActiveApplication()->getAbbreviation()}{/link}{else}{link}{/link}{/if}" class="pageHeaderBarLogo" aria-label="{PAGE_TITLE|phrase}">
						<img src="{$__wcf->getStyleHandler()->getStyle()->getPageLogoMobile()}" alt="" class="pageHeaderLogoSmall"{*
							*}{if $__wcf->getStyleHandler()->getStyle()->getPageLogoSmallHeight()} height="{$__wcf->getStyleHandler()->getStyle()->getPageLogoSmallHeight()}"{/if}{*
							*}{if $__wcf->getStyleHandler()->getStyle()->getPageLogoSmallWidth()} width="{$__wcf->getStyleHandler()->getStyle()->getPageLogoSmallWidth()}"{/if}{*
							*} loading="eager">
					</a>
				{/if}
				
				{unsafe:$__wcf->getBoxHandler()->getBoxByIdentifier('com.woltlab.wcf.MainMenu')->render()}
				
				{include file='pageHeaderSearch'}
				
				{include file='pageHeaderUser'}
			</div>
		</div>
	</header>
	
	{if $__pageHeaderLayout === 'logoBelow'}
		<div id="pageHeaderFacade" class="pageHeaderFacade">
			<div class="layoutBoundary">
				{include file='system_pageHeaderLogo'}
			</div>
		</div>
	{/if}
	
	{hascontent}
		<div class="boxesHero">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{if !$boxesHero|empty}
							{unsafe:$boxesHero}
						{/if}

						{foreach from=$__wcf->getBoxHandler()->getBoxes('hero') item=box}
							{unsafe:$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
</div>

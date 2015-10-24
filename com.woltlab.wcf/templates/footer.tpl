				{event name='contents'}
				
				{if MODULE_WCF_AD && $__disableAds|empty}
					{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.content')}
				{/if}
			</div>
			
			{if $sidebarOrientation|isset && $sidebarOrientation == 'right'}
				{@$__sidebar}
			{/if}
		</div>
	</section>
	
	<div id="pageFooterBoxes">
		<div class="layoutBoundary">
			{hascontent}
				<ul>
					{content}{if !$footerBoxes|empty}{@$footerBoxes}{/if}{/content}
				</ul>
			{/hascontent}
		</div>
	</div>
	
	{*include file='pageNavbarBottom'*}
	
	{*<nav id="footerNavigation" class="navigation navigationFooter">
		{include file='footerMenu'}
		
		<ul class="navigationIcons">
			<li id="toTopLink" class="toTopLink"><a href="{$__wcf->getAnchor('top')}" title="{lang}wcf.global.scrollUp{/lang}" class="jsTooltip"><span class="icon icon16 icon-arrow-up"></span> <span class="invisible">{lang}wcf.global.scrollUp{/lang}</span></a></li>
			{event name='navigationIcons'}
		</ul>
		
		<ul class="navigationItems">
			{if SHOW_CLOCK}
				<li title="{lang}wcf.date.timezone.{@'/'|str_replace:'.':$__wcf->getUser()->getTimeZone()->getName()|strtolower}{/lang}"><p><span class="icon icon16 icon-time"></span> <span>{@TIME_NOW|plainTime}</span></p></li>
			{/if}
			{event name='navigationItems'}
		</ul>
	</nav>*}
	
	<footer id="pageFooter" class="footer{if $sidebarOrientation|isset && $sidebar|isset} sidebarOrientation{@$sidebarOrientation|ucfirst}{if $sidebarOrientation == 'right' && $sidebarCollapsed} sidebarCollapsed{/if}{/if}">
		<div class="layoutBoundary">
			<div class="footerContent">
				{event name='footerContents'}
				
				{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
				
				{event name='copyright'}
			</div>
			
			{if MODULE_WCF_AD && $__disableAds|empty}
				{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
			{/if}
		</div>
	</footer>
</div>

{event name='footer'}

<!-- JAVASCRIPT_RELOCATE_POSITION -->

{@FOOTER_CODE}

<a id="bottom"></a>

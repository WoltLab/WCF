<footer id="pageFooter" class="pageFooter">
	{assign var=__boxesFooter value=$__wcf->getBoxHandler()->getBoxes('footer')}
	{if $__wcf->getStyleHandler()->showStyleChanger() && $__wcf->getStyleHandler()->countStyles() > 1}
		{assign var=__showStyleChanger value=true}
	{else}
		{assign var=__showStyleChanger value=false}
	{/if}
	
	{if $__boxesFooter|count || $__showStyleChanger}
		<div class="boxesFooter">
			<div class="layoutBoundary{if $__showStyleChanger} clearfix{/if}">
				{if $__showStyleChanger}
					<span class="styleChanger">
						<a href="#" class="jsButtonStyleChanger">{lang}wcf.style.changeStyle{/lang}</a>
					</span>
				{/if}
				{if $__boxesFooter|count}
					<div class="boxContainer">
						{foreach from=$__boxesFooter item=box}
							{@$box->render()}
						{/foreach}
					</div>
				{/if}
			</div>
		</div>
	{/if}
	
	{hascontent}
		<div id="pageFooterCopyright" class="pageFooterCopyright">
			<div class="layoutBoundary">
				{content}
					{event name='footerContents'}
					
					{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
					
					{include file='pageFooterCopyright'}
				
					{if MODULE_WCF_AD && $__disableAds|empty}
						{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
					{/if}
				{/content}
			</div>
		</div>
	{/hascontent}
</footer>

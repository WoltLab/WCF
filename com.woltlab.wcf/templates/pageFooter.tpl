<footer id="pageFooter" class="pageFooter">
	{assign var=__boxesFooter value=$__wcf->getBoxHandler()->getBoxes('footer')}
	{assign var=__showStyleChanger value=$__wcf->getStyleHandler()->showStyleChanger()}
	
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
				{/content}
			</div>
		</div>
	{/hascontent}
	
	{if MODULE_WCF_AD && $__disableAds|empty}
		{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
	{/if}
</footer>

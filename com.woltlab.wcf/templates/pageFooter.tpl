<footer id="pageFooter" class="pageFooter">
	{hascontent}
		<div class="boxesFooter">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footer') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
	
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

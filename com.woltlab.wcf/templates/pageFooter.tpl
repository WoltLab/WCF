<footer id="pageFooter" class="pageFooter">
	<div class="layoutBoundary">
		{hascontent}
			<div class="boxesFooter">
				<div class="boxContainer">
					{content}
						{foreach from=$__wcf->getBoxHandler()->getBoxes('footer') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>	
			</div>
		{/hascontent}
		
		<div class="footerContent">
			{event name='footerContents'}
			
			{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
			
			{include file='pageFooterCopyright'}
		</div>
		
		{if MODULE_WCF_AD && $__disableAds|empty}
			{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.footer.bottom')}
		{/if}
	</div>
</footer>

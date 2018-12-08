<footer id="pageFooter" class="pageFooter">
	{if !$errorField|empty || !$errorType|empty}
		<!--
			DEBUG: FORM_VALIDATION_FAILED
			
			errorField: {if $errorField|empty}(empty){else}{$errorField|print_r:true}{/if}
			
			errorType: {if $errorType|empty}(empty){else}{$errorType|print_r:true}{/if}
		
		-->
	{/if}
	
	{assign var=__boxesFooter value=$__wcf->getBoxHandler()->getBoxes('footer')}
	{if $__wcf->getStyleHandler()->showStyleChanger() && $__wcf->getStyleHandler()->countStyles() > 1}
		{assign var=__showStyleChanger value=true}
	{else}
		{assign var=__showStyleChanger value=false}
	{/if}
	
	{if $__boxesFooter|count || !$boxesFooter|empty || $__showStyleChanger}
		<div class="boxesFooter">
			<div class="layoutBoundary{if $__showStyleChanger} clearfix{/if}">
				{if $__showStyleChanger}
					<span class="styleChanger jsOnly">
						<a href="#" class="jsButtonStyleChanger">{lang}wcf.style.changeStyle{/lang}</a>
					</span>
				{/if}
				{hascontent}
					<div class="boxContainer">
						{content}
							{if !$boxesFooter|empty}
								{@$boxesFooter}
							{/if}

							{foreach from=$__boxesFooter item=box}
								{@$box->render()}
							{/foreach}
						{/content}
					</div>
				{/hascontent}
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

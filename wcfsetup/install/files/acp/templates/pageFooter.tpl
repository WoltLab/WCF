<footer id="pageFooter" class="pageFooter">
	{if !$errorField|empty || !$errorType|empty}
		<!--
			DEBUG: FORM_VALIDATION_FAILED
			
			errorField: {if $errorField|empty}(empty){else}{$errorField|print_r:true}{/if}
			
			errorType: {if $errorType|empty}(empty){else}{$errorType|print_r:true}{/if}
		
		-->
	{/if}
	
	<div id="pageFooterCopyright" class="pageFooterCopyright">
		<div class="layoutBoundary">
			{event name='footerContents'}

			{if ENABLE_BENCHMARK}{include file='shared_benchmark'}{/if}
			
			{event name='copyright'}
			
			<div class="copyright">{lang}wcf.page.copyright{/lang}</div>
		</div>
	</div>
</footer>

<footer id="pageFooter" class="pageFooter">
	<div id="pageFooterCopyright" class="pageFooterCopyright">
		<div class="layoutBoundary">
			{event name='footerContents'}
			
			{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
			
			{event name='copyright'}
			
			<div class="copyright">{lang}wcf.page.copyright{/lang}</div>
		</div>
	</div>
</footer>

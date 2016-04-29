<footer id="pageFooter" class="pageFooter">
	<div id="pageFooterCopyright" class="pageFooterCopyright">
		<div class="layoutBoundary">
			{event name='footerContents'}
			
			{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
			
			<div class="copyright"><a href="https://www.woltlab.com">Copyright &copy; 2001-2016 WoltLab&reg; GmbH</a>{event name='copyright'}</div>
		</div>
	</div>
</footer>

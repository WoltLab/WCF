<footer id="pageFooter" class="pageFooter">
	<div class="layoutBoundary">
		<div class="footerContent">
			{event name='footerContents'}
			
			{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
			
			<address class="copyright"><a href="http://www.woltlab.com">Copyright &copy; 2001-2015 WoltLab&reg; GmbH</a>{event name='copyright'}</address>
		</div>
	</div>
</footer>

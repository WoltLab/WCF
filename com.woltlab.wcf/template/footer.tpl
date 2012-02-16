				{if $skipBreadcrumbs|empty}{include file='breadcrumbs' sandbox=false __microdata=false}{/if}
				
			</section>
			<!-- /CONTENT -->
		</div>
	</div>
	<!-- /MAIN -->
	
	<!-- FOOTER -->
	<footer id="pageFooter" class="wcf-pageFooter">
		<div>
			{include file='footerMenu'}
		</div>
		
		{if ENABLE_BENCHMARK}{include file='benchmark'}{/if}
		
		{event name='copyright'}
	</footer>
	<!-- /FOOTER -->
	<a id="bottom"></a>
	
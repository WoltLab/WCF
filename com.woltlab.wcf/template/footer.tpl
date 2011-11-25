				{include file='breadcrumbs' sandbox=false}
				
			</section>
			<!-- /CONTENT -->
		</div>
	</div>
	<!-- /MAIN -->
	
	<!-- FOOTER -->
	<footer id="pageFooter" class="pageFooter">
		<div>
			{include file=footerMenu}
		</div>
		
		<p style="margin-top: 10px; font-size: .85em" id="benchmark">Execution time: {@$__wcf->getBenchmark()->getExecutionTime()}s ({#($__wcf->getBenchmark()->getExecutionTime()-$__wcf->getBenchmark()->getQueryExecutionTime())/$__wcf->getBenchmark()->getExecutionTime()*100}% PHP, {#$__wcf->getBenchmark()->getQueryExecutionTime()/$__wcf->getBenchmark()->getExecutionTime()*100}% SQL) | SQL queries: {#$__wcf->getBenchmark()->getQueryCount()}</p>
		
		<ul id="benchmarkDetails" style="display: none;">
			{foreach from=$__wcf->getBenchmark()->getItems() item=item}
				<li style="margin-bottom: 8px;{if $item.use >= 0.01} color: #b00{/if}">{if $item.type == 1}(SQL Query) {/if}{$item.text}<br /><small style="font-size: .85em">Execution time: {@$item.use}s</small></li>
			{/foreach}
		</ul>
		
		<script type="text/javascript">
			//<![CDATA[
			$(function() {
				$('#benchmark').click(function() {
					WCF.showDialog('benchmarkDetails', true, {
						title: 'Log'
					});
					return false;
				});
			});
			//]]>
		</script>
		
		{event name='copyright'}
	</footer>
	<!-- /FOOTER -->
	<a id="bottom"></a>
	

<p id="benchmark" class="wcf-marginTop" style="font-size: .85em">Execution time: {@$__wcf->getBenchmark()->getExecutionTime()}s ({#($__wcf->getBenchmark()->getExecutionTime()-$__wcf->getBenchmark()->getQueryExecutionTime())/$__wcf->getBenchmark()->getExecutionTime()*100}% PHP, {#$__wcf->getBenchmark()->getQueryExecutionTime()/$__wcf->getBenchmark()->getExecutionTime()*100}% SQL) | SQL queries: {#$__wcf->getBenchmark()->getQueryCount()} | Memory-Usage: {$__wcf->getBenchmark()->getMemoryUsage()}</p>

{if ENABLE_DEBUG_MODE}	
	<ul id="benchmarkDetails" style="display: none;">
		{foreach from=$__wcf->getBenchmark()->getItems() item=item}
			<li style="margin-bottom: 8px;"{if $item.use >= 0.01} class="hot"{/if}>{if $item.type == 1}(SQL Query) {/if}{$item.text}<br /><small style="font-size: .85em">Execution time: {@$item.use}s</small></li>
		{/foreach}
	</ul>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			$('#benchmark').click(function() {
				WCF.showDialog('benchmarkDetails', {
					title: 'Log'
				});
				return false;
			});
		});
		//]]>
	</script>
{/if}
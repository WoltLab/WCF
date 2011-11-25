<p style="margin-top: 10px; font-size: .85em" id="benchmark">Execution time: {@$__wcf->getBenchmark()->getExecutionTime()}s ({#($__wcf->getBenchmark()->getExecutionTime()-$__wcf->getBenchmark()->getQueryExecutionTime())/$__wcf->getBenchmark()->getExecutionTime()*100}% PHP, {#$__wcf->getBenchmark()->getQueryExecutionTime()/$__wcf->getBenchmark()->getExecutionTime()*100}% SQL) | SQL queries: {#$__wcf->getBenchmark()->getQueryCount()}</p>

{if ENABLE_DEBUG_MODE}	
	<ul id="benchmarkDetails" style="display: none; max-height: 500px; overflow: auto">
		{foreach from=$__wcf->getBenchmark()->getItems() item=item}
			<li style="margin-bottom: 8px;"{if $item.use >= 0.01} class="hot"{/if}>{if $item.type == 1}(SQL Query) {/if}{$item.text}<br /><small style="font-size: .85em">Execution time: {@$item.use}s</small></li>
		{/foreach}
	</ul>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			$('#benchmark').click(function() {
				WCF.showDialog('benchmarkDetails', true);
				return false;
			});
		});
		//]]>
	</script>
{/if}
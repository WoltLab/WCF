<div class="benchmark">
	{if ENABLE_DEBUG_MODE}<a id="benchmark">{/if}Execution time: {@$__wcf->getBenchmark()->getExecutionTime()}s ({#($__wcf->getBenchmark()->getExecutionTime()-$__wcf->getBenchmark()->getQueryExecutionTime())/$__wcf->getBenchmark()->getExecutionTime()*100}% PHP, {#$__wcf->getBenchmark()->getQueryExecutionTime()/$__wcf->getBenchmark()->getExecutionTime()*100}% SQL) | SQL queries: {#$__wcf->getBenchmark()->getQueryCount()} | Memory-Usage: {$__wcf->getBenchmark()->getMemoryUsage()}{if ENABLE_DEBUG_MODE}</a>{/if}
	
	{if ENABLE_DEBUG_MODE}
		<script data-relocate="true">
			//<![CDATA[
			$(function() {
				$('#benchmarkDetails > li > span').click(function() {
					$(this).parent().children('pre').toggle();
				});
			});
			//]]>
		</script>
		<ul id="benchmarkDetails" style="display: none;">
			{foreach from=$__wcf->getBenchmark()->getItems() key=benchmarkIndex item=item}
				<li id="benchmarkItem{@$benchmarkIndex}" style="margin-bottom: 8px;"{if $item.use >= 0.01} class="hot"{/if}>
					<span>{if $item.type == 1}(SQL Query) {/if}{$item.text}</span><br>
					<small style="font-size: .85em">Execution time: {@$item.use}s</small>
				
				<pre style="display: none">{foreach from=$item.trace key=traceNo item=traceItem}#{#$traceNo} {if !$traceItem.class|empty}{$traceItem.class}{$traceItem.type}{else}{if !$traceItem.file|empty}{$traceItem.file}: {/if}{/if}{$traceItem.function}() {if !$traceItem.line|empty}(line {#$traceItem.line}){/if} 
{/foreach}</pre>
				
				</li>
			{/foreach}
		</ul>
		
		<script data-relocate="true">
			//<![CDATA[
			$(function() {
				$('#benchmark').click(function() {
					$('#benchmarkDetails').wcfDialog({
						title: 'Log'
					});
				});
			});
			//]]>
		</script>
	{/if}
</div>
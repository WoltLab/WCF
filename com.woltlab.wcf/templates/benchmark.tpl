<div class="benchmark">
	{if ENABLE_DEBUG_MODE}<button class="benchmarkDetailsButton">{/if}
		Execution time: {@$__wcf->getBenchmark()->getExecutionTime()}s ({#($__wcf->getBenchmark()->getExecutionTime()-$__wcf->getBenchmark()->getQueryExecutionTime())/$__wcf->getBenchmark()->getExecutionTime()*100}% PHP, {#$__wcf->getBenchmark()->getQueryExecutionTime()/$__wcf->getBenchmark()->getExecutionTime()*100}% SQL) | SQL queries: {#$__wcf->getBenchmark()->getQueryCount()} | Memory-Usage: {$__wcf->getBenchmark()->getMemoryUsage()}
	{if ENABLE_DEBUG_MODE}</button>{/if}
	
	{if ENABLE_DEBUG_MODE}
		<div class="benchmarkDetails" hidden>
			<dl{if $__wcf->getBenchmark()->getOffsetToRequestTime() >= 0.5} class="hot"{/if}>
				<dt>Request Execution time</dt>
				<dd>{#$__wcf->getBenchmark()->getRequestExecutionTime()}s</dd>
				<dt>Benchmark started after</dt>
				<dd>{#$__wcf->getBenchmark()->getOffsetToRequestTime()}s</dd>
			</dl>
			<hr>
			<ol class="nativeList">
				{foreach from=$__wcf->getBenchmark()->getItems() key=benchmarkIndex item=item}
					<li {if $item.use >= 0.01} class="hot"{/if}>
						<details>
							<summary>
								<span>{if $item.type == 1}(SQL Query) {/if}{$item.text}</span><br>
								<small style="font-size: .85em">Execution time: <kbd>{@$item.use}s</kbd></small>
							</summary>
					
							<ol class="nativeList" start="0">
								{foreach from=$item.trace item=traceItem}
									<li>
										{if !$traceItem.class|empty}{$traceItem.class}{$traceItem.type}{else}{if !$traceItem.file|empty}{$traceItem.file}: {/if}{/if}{$traceItem.function}() {if !$traceItem.line|empty}(line {#$traceItem.line}){/if}
									</li>
								{/foreach}
							</ol>
						</details>
					</li>
				{/foreach}
			</ol>
		</div>
		
		<script data-relocate="true">
			require(['WoltLabSuite/Core/Component/Dialog'], ({ dialogFactory }) => {
				const details = document.querySelector('.benchmark .benchmarkDetails');
				const dialog = dialogFactory().fromElement(details).withoutControls();

				document.querySelector('.benchmark .benchmarkDetailsButton').addEventListener('click', () => {
					dialog.show('Log');
				});
			});
		</script>
	{/if}
</div>

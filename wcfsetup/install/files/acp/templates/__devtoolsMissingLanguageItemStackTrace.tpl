<ol class="nativeList" start="0">
	{foreach from=$stackTrace item=stackEntry}
		<li>
			<strong>{$stackEntry['class']}</strong>{$stackEntry['type']}{$stackEntry['function']}({@$stackEntry['args']})<br>
			<small>{$stackEntry[file]} ({$stackEntry[line]})</small>
		</li>
	{/foreach}
</ol>

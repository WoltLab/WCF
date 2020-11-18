
<tr>
	<td class="columnText">{$device[deviceName]}</td>
	<td class="columnDate">{$device[createTime]|plainTime}</td>
	<td class="columnDate">{if $device[useTime]}{$device[useTime]|plainTime}{else}&ndash;{/if}</td>
	<td class="columnText">
		{foreach from=$container item='child'}
			{if $child->isAvailable()}
				{@$child->getHtml()}
			{/if}
		{/foreach}
	</td>
</tr>

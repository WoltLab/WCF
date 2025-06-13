
<tr>
	<td class="columnText">{$device[deviceName]}</td>
	<td class="columnDate">{time time=$device[createTime] type='plainTime'}</td>
	<td class="columnDate">{if $device[useTime]}{time time=$device[useTime] type='plainTime'}{else}&ndash;{/if}</td>
	<td class="columnText">
		{foreach from=$container item='child'}
			{if $child->isAvailable()}
				{unsafe:$child->getHtml()}
			{/if}
		{/foreach}
	</td>
</tr>

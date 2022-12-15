<div id="userActivityPointListContainer" class="section tabularBox">
	<table class="table">
		<thead>
			<tr>
				<th>{lang}wcf.user.activityPoint.objectType{/lang}</th>
				<th>{lang}wcf.user.activityPoint.objects{/lang}</th>
				<th>{lang}wcf.user.activityPoint.pointsPerObject{/lang}</th>
				<th>{lang}wcf.user.activityPoint.sum{/lang}</th>
			</tr>
		</thead>
		
		<tbody>
			{foreach from=$entries item='entry'}
				{if $entry['activityPoints'] > 0 && $entry['objectType']->points > 0}
					<tr>
						<td class="columnTitle">
							{lang}wcf.user.activityPoint.objectType.{$entry['objectType']->objectType}{/lang}
						</td>
						<td class="columnDigits">
							{#$entry['items']}
						</td>
						<td class="columnDigits">
							{#$entry['objectType']->points}
						</td>
						<td class="columnDigits">
							{#$entry['activityPoints']}
						</td>
					</tr>
				{/if}
			{/foreach}
			
			<tr>
				<td class="columnTitle focus right" colspan="3">&sum;</td>
				<td class="columnDigits focus"><span class="badge">{#$user->activityPoints}</span></td>
			</tr>
		</tbody>
	</table>
</div>

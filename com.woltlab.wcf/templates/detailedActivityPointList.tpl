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
			{assign var='activityPointSum' value=0}
			{foreach from=$activityPointObjectTypes item='objectType'}
				{if $objectType->activityPoints > 0 && $objectType->points > 0}
					<tr>
						<td class="columnTitle">
							{lang}wcf.user.activityPoint.objectType.{$objectType->objectType}{/lang}
						</td>
						<td class="columnDigits">
							{#$objectType->items}
						</td>
						<td class="columnDigits">
							{#$objectType->points}
						</td>
						<td class="columnDigits">
							{#$objectType->activityPoints}
						</td>
						{assign var='activityPointSum' value=$activityPointSum + $objectType->activityPoints}
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

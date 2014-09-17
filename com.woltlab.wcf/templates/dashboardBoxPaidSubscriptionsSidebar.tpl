<ul class="sidebarBoxList">
	{foreach from=$subscriptions item=subscription}
		<li>
			<div class="sidebarBoxHeadline" title="{$subscription->description|language}">
				<h3>{$subscription->title|language}</h3>
				<small>{lang}wcf.paidSubscription.formattedCost{/lang}</small> 
			</div>
			
			<ul class="buttonList marginTopTiny">
				{foreach from=$subscription->getPurchaseButtons() item=button}
					<li>{@$button}</li>
				{/foreach}
			</ul>
		</li>
	{/foreach}
</ul>

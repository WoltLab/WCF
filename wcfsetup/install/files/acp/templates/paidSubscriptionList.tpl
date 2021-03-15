{include file='header' pageTitle='wcf.acp.paidSubscription.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\paid\\subscription\\PaidSubscriptionAction', '.jsPaidSubscriptionRow');
		new WCF.Action.Toggle('wcf\\data\\paid\\subscription\\PaidSubscriptionAction', '.jsPaidSubscriptionRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PaidSubscriptionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.paidSubscription.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='PaidSubscriptionList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSubscriptionID{if $sortField == 'subscriptionID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PaidSubscriptionList'}pageNo={@$pageNo}&sortField=subscriptionID&sortOrder={if $sortField == 'subscriptionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle{if $sortField == 'title'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionList'}pageNo={@$pageNo}&sortField=title&sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.title{/lang}</a></th>
					<th class="columnDigits columnCost{if $sortField == 'cost'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionList'}pageNo={@$pageNo}&sortField=cost&sortOrder={if $sortField == 'cost' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.paidSubscription.cost{/lang}</a></th>
					<th class="columnDigits columnSubscriptionLength{if $sortField == 'subscriptionLength'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionList'}pageNo={@$pageNo}&sortField=subscriptionLength&sortOrder={if $sortField == 'subscriptionLength' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.paidSubscription.subscriptionLength{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=subscription}
					<tr class="jsPaidSubscriptionRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$subscription->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$subscription->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$subscription->subscriptionID}"></span>
							<a href="{link controller='PaidSubscriptionEdit' id=$subscription->subscriptionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$subscription->subscriptionID}" data-confirm-message-html="{lang __encode=true}wcf.acp.paidSubscription.delete.confirmMessage{/lang}"></span>
							<a href="{link controller='PaidSubscriptionUserAdd' id=$subscription->subscriptionID}{/link}" title="{lang}wcf.acp.paidSubscription.user.add{/lang}" class="jsTooltip"><span class="icon icon16 fa-plus"></span></a>
							
							{event name='itemButtons'}
						</td>
						<td class="columnID columnSubscriptionID">{@$subscription->subscriptionID}</td>
						<td class="columnTitle"><a href="{link controller='PaidSubscriptionEdit' id=$subscription->subscriptionID}{/link}" title="{lang}wcf.acp.paidSubscription.edit{/lang}">{$subscription->getTitle()}</a></td>
						<td class="columnDigits columnCost">{@$subscription->currency} {$subscription->cost|currency}</td>
						<td class="columnDigits columnSubscriptionLength">{if $subscription->subscriptionLength}{@$subscription->subscriptionLength} {lang}wcf.acp.paidSubscription.subscriptionLengthUnit.{@$subscription->subscriptionLengthUnit}{/lang}{else}&infin;{/if}</td>
						<td class="columnDigits columnShowOrder">{@$subscription->showOrder}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='PaidSubscriptionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.paidSubscription.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

{include file='header' pageTitle='wcf.acp.paidSubscription.user.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\paid\\subscription\\user\\PaidSubscriptionUserAction', '.jsPaidSubscriptionUserRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='PaidSubscriptionUserList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSubscriptionUserID{if $sortField == 'subscriptionUserID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionUserID&sortOrder={if $sortField == 'subscriptionUserID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'userID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnSubscriptionTitle{if $sortField == 'subscriptionID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionID&sortOrder={if $sortField == 'subscriptionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.paidSubscription.subscription{/lang}</a></th>
					<th class="columnDate columnEndDate{if $sortField == 'endDate'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=endDate&sortOrder={if $sortField == 'endDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.paidSubscription.user.endDate{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=subscriptionUser}
					<tr class="jsPaidSubscriptionUserRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$subscriptionUser->subscriptionUserID}" data-confirm-message="{lang}wcf.acp.paidSubscription.user.delete.confirmMessage{/lang}"></span>
							
							{event name='itemButtons'}
						</td>
						<td class="columnID columnSubscriptionUserID">{@$subscriptionUser->subscriptionUserID}</td>
						<td class="columnText columnUsername"><a href="{link controller='UserEdit' id=$subscriptionUser->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$subscriptionUser->username}</a></td>
						<td class="columnText columnSubscriptionTitle">{$subscriptionUser->title|language}</td>
						<td class="columnDate columnEndDate">{if $subscriptionUser->endDate}{@$subscriptionUser->endDate|time}{/if}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{hascontent}
			<nav>
				<ul>
					{content}
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

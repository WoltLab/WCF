{include file='header' pageTitle='wcf.acp.paidSubscription.user.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('username'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='PaidSubscriptionUserList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long">
				</dd>
			</dl>
			
			{if $availableSubscriptions|count > 1}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<select name="subscriptionID" id="subscriptionID">
							<option value="0">{lang}wcf.acp.paidSubscription.subscription{/lang}</option>
							{foreach from=$availableSubscriptions item=availableSubscription}
								<option value="{@$availableSubscription->subscriptionID}"{if $availableSubscription->subscriptionID == $subscriptionID} selected{/if}>{$availableSubscription->getTitle()}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
			{/if}
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>

{assign var='linkParameters' value=''}
{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
{if $subscriptionID}{capture append=linkParameters}&subscriptionID={@$subscriptionID}{/capture}{/if}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='PaidSubscriptionUserList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\paid\subscription\user\PaidSubscriptionUserAction"
			<thead>
				<tr>
					<th class="columnID columnSubscriptionUserID{if $sortField == 'subscriptionUserID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionUserID&sortOrder={if $sortField == 'subscriptionUserID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnSubscriptionTitle{if $sortField == 'subscriptionID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionID&sortOrder={if $sortField == 'subscriptionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.subscription{/lang}</a></th>
					<th class="columnDate columnEndDate{if $sortField == 'endDate'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=endDate&sortOrder={if $sortField == 'endDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.user.endDate{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=subscriptionUser}
					<tr class="jsPaidSubscriptionUserRow jsObjectActionObject" data-object-id="{@$subscriptionUser->getObjectID()}">
						<td class="columnIcon">
							{if $subscriptionUser->endDate}
								<a href="{link controller='PaidSubscriptionUserEdit' id=$subscriptionUser->subscriptionUserID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{else}
								<span class="icon icon16 fa-pencil disabled"></span>
							{/if}
							{objectAction action="delete" confirmMessage='wcf.acp.paidSubscription.user.delete.confirmMessage'}
							
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
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}{event name='contentFooterNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

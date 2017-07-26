{include file='header' pageTitle='wcf.acp.paidSubscription.user.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\paid\\subscription\\user\\PaidSubscriptionUserAction', '.jsPaidSubscriptionUserRow');
		new WCF.Search.User('#username');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.user.list{/lang}  <span class="badge badgeInverse">{#$items}</span></h1>
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
								<option value="{@$availableSubscription->subscriptionID}"{if $availableSubscription->subscriptionID == $subscriptionID} selected{/if}>{$availableSubscription->title|language}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
			{/if}
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{@SECURITY_TOKEN_INPUT_TAG}
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
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSubscriptionUserID{if $sortField == 'subscriptionUserID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionUserID&sortOrder={if $sortField == 'subscriptionUserID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnSubscriptionTitle{if $sortField == 'subscriptionID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=subscriptionID&sortOrder={if $sortField == 'subscriptionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.subscription{/lang}</a></th>
					<th class="columnDate columnEndDate{if $sortField == 'endDate'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionUserList'}pageNo={@$pageNo}&sortField=endDate&sortOrder={if $sortField == 'endDate' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.user.endDate{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=subscriptionUser}
					<tr class="jsPaidSubscriptionUserRow">
						<td class="columnIcon">
							{if $subscriptionUser->endDate}
								<a href="{link controller='PaidSubscriptionUserEdit' id=$subscriptionUser->subscriptionUserID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{else}
								<span class="icon icon16 fa-pencil disabled"></span>
							{/if}
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$subscriptionUser->subscriptionUserID}" data-confirm-message-html="{lang __encode=true}wcf.acp.paidSubscription.user.delete.confirmMessage{/lang}"></span>
							
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

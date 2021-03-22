{include file='header' pageTitle='wcf.acp.paidSubscription.transactionLog.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('username'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.paidSubscription.transactionLog.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='PaidSubscriptionTransactionLogList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="transactionID" name="transactionID" value="{$transactionID}" placeholder="{lang}wcf.acp.paidSubscription.transactionLog.transactionID{/lang}" class="long">
				</dd>
			</dl>
			
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

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $transactionID}{capture append=linkParameters}&transactionID={@$transactionID|rawurlencode}{/capture}{/if}
			{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
			{if $subscriptionID}{capture append=linkParameters}&subscriptionID={@$subscriptionID}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller='PaidSubscriptionTransactionLogList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLogID{if $sortField == 'logID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=logID&sortOrder={if $sortField == 'logID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnLogMessage{if $sortField == 'logMessage'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=logMessage&sortOrder={if $sortField == 'logMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.transactionLog.logMessage{/lang}</a></th>
					<th class="columnText columnUsername{if $sortField == 'userID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnText columnSubscriptionTitle{if $sortField == 'subscriptionID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=subscriptionID&sortOrder={if $sortField == 'subscriptionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.subscription{/lang}</a></th>
					<th class="columnText columnPaymentMethod{if $sortField == 'paymentMethodObjectTypeID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=paymentMethodObjectTypeID&sortOrder={if $sortField == 'paymentMethodObjectTypeID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.transactionLog.paymentMethod{/lang}</a></th>
					<th class="columnText columnTransactionID{if $sortField == 'transactionID'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=transactionID&sortOrder={if $sortField == 'transactionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.transactionLog.transactionID{/lang}</a></th>
					<th class="columnDate columnLogTime{if $sortField == 'logTime'} active {@$sortOrder}{/if}"><a href="{link controller='PaidSubscriptionTransactionLogList'}pageNo={@$pageNo}&sortField=logTime&sortOrder={if $sortField == 'logTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.paidSubscription.transactionLog.logTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=log}
					<tr>
						<td class="columnID columnLogID">{@$log->logID}</td>
						<td class="columnTitle columnLogMessage"><a href="{link controller='PaidSubscriptionTransactionLog' id=$log->logID}{/link}">{$log->logMessage}</a></td>
						<td class="columnText columnUsername"><a href="{link controller='UserEdit' id=$log->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$log->username}</a></td>
						<td class="columnText columnSubscriptionTitle">{$log->title|language}</td>
						<td class="columnText columnPaymentMethod">{lang}wcf.payment.{@$log->getPaymentMethodName()}{/lang}</td>
						<td class="columnText columnTransactionID">{$log->transactionID}</td>
						<td class="columnDate columnLogTime">{@$log->logTime|time}</td>
						
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

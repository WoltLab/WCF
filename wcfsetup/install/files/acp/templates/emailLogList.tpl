{include file='header' pageTitle='wcf.acp.email.log'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.email.log{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='EmailLogList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input
						class="long"
						name="filter[username]"
						placeholder="{lang}wcf.user.username{/lang}"
						type="text"
						value="{$filter[username]}"
					>

					<script data-relocate="true">
						require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
							new UiUserSearchInput(document.querySelector('input[name="filter[username]"]'));
						});
					</script>
				</dd>
			</dl>

			{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
				<dl class="col-xs-12 col-md-4">
					<dt></dt>
					<dd>
						<input
							class="long"
							name="filter[email]"
							placeholder="{lang}wcf.user.email{/lang}"
							type="email"
							value="{$filter[email]}"
						>
					</dd>
				</dl>
			{/if}

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="filter[status]" aria-label="{lang}wcf.acp.email.log.status{/lang}">
						<option value="">{lang}wcf.acp.email.log.status{/lang}</option>
						<option value="new"{if $filter[status] == 'new'} selected{/if}>{lang}wcf.acp.email.log.status.new{/lang}</option>
						<option value="success"{if $filter[status] == 'success'} selected{/if}>{lang}wcf.acp.email.log.status.success{/lang}</option>
						<option value="transient_failure"{if $filter[status] == 'transient_failure'} selected{/if}>{lang}wcf.acp.email.log.status.transient_failure{/lang}</option>
						<option value="permanent_failure"{if $filter[status] == 'permanent_failure'} selected{/if}>{lang}wcf.acp.email.log.status.permanent_failure{/lang}</option>
						<option value="discarded"{if $filter[status] == 'discarded'} selected{/if}>{lang}wcf.acp.email.log.status.discarded{/lang}</option>
					</select>
				</dd>
			</dl>
		</div>

		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-8">
				<dt></dt>
				<dd>
					<input
						class="long"
						name="filter[messageId]"
						placeholder="{lang}wcf.acp.email.log.messageId{/lang}"
						type="text"
						value="{$filter[messageId]}"
					>
				</dd>
			</dl>
		</div>

		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller="EmailLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&$filterParameter"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnEntryID{if $sortField == 'entryID'} active {@$sortOrder}{/if}"><a href="{link controller='EmailLogList'}pageNo={@$pageNo}&sortField=entryID&sortOrder={if $sortField == 'entryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&{@$filterParameter}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnSubject{if $sortField == 'subject'} active {@$sortOrder}{/if}">{lang}wcf.acp.email.log.subject{/lang}</th>
					<th class="columnText columnRecipient{if $sortField == 'recipient'} active {@$sortOrder}{/if}">{lang}wcf.user.email{/lang}</th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='EmailLogList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&{@$filterParameter}{/link}">{lang}wcf.acp.email.log.time{/lang}</a></th>
					<th class="columnText columnStatusMessage{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link controller='EmailLogList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&{@$filterParameter}{/link}">{lang}wcf.acp.email.log.status{/lang}</a></th>

					{event name='columnHeads'}
				</tr>
			</thead>

			<tbody>
				{foreach from=$objects item=entry}
					<tr class="jsEmailLogEntry">
						<td class="columnID columnEntryID">{@$entry->entryID}</td>
						<td class="columnTitle columnMessageID">
							{$entry->subject}<br>
							<small>
								<kbd title="{$entry->messageID}">{$entry->getFormattedMessageId()|truncate:50}</kbd>
							</small>
						</td>
						<td class="columnText columnRecipient">
							{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
								{$entry->recipient}
							{else}
								{$entry->getRedactedRecipientAddress()}
							{/if}
							{if $entry->getRecipient()}
								<br>
								<small>
									<a href="{link controller='EmailLogList'}filter[username]={$entry->getRecipient()->username}{/link}">{$entry->getRecipient()->getTitle()}</a>
								</small>
							{/if}
						</td>
						<td class="columnDate columnTime">{@$entry->time|time}</td>

						<td class="columnText columnStatusMessage">
							<span class="
								badge
								{if $entry->status === 'success'}green
								{elseif $entry->status === 'transient_failure'}yellow
								{elseif $entry->status === 'permanent_failure'}red
								{/if}
								{if $entry->message}pointer jsStaticDialog{/if}
							"{if $entry->message} data-dialog-id="statusMessage{$entry->entryID}"{/if}>{lang}wcf.acp.email.log.status.{$entry->status}{/lang}</span>
							{if $entry->message}
								<div id="statusMessage{$entry->entryID}" data-title="{lang}wcf.acp.email.log.statusMessage.title{/lang}" style="display: none">
									{$entry->message}
								</div>
							{/if}
						</td>

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
					{content}
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info" role="status">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

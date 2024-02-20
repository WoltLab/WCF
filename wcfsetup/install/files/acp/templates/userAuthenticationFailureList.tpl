{include file='header' pageTitle='wcf.acp.user.authentication.failure.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.authentication.failure.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='UserAuthenticationFailureList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="filter[environment]" id="environment">
						<option value="">{lang}wcf.acp.user.authentication.failure.environment{/lang}</option>
						<option value="admin"{if $filter[environment] === 'admin'} selected{/if}>{lang}wcf.acp.user.authentication.failure.environment.admin{/lang}</option>
						<option value="user"{if $filter[environment] === 'user'} selected{/if}>{lang}wcf.acp.user.authentication.failure.environment.user{/lang}</option>
					</select>
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="filter[username]" value="{$filter[username]}" placeholder="{lang}wcf.user.username{/lang}" class="long">
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="filter[validationError]" id="validationError">
						<option value="">{lang}wcf.acp.user.authentication.failure.validationError{/lang}</option>
						<option value="invalidPassword"{if $filter[validationError] === 'invalidPassword'} selected{/if}>{lang}wcf.acp.user.authentication.failure.validationError.invalidPassword{/lang}</option>
						<option value="invalidUsername"{if $filter[validationError] === 'invalidUsername'} selected{/if}>{lang}wcf.acp.user.authentication.failure.validationError.invalidUsername{/lang}</option>
						{event name='validationErrorFilterOptions'}
					</select>
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" id="startDate" name="filter[startDate]" value="{$filter[startDate]}" data-placeholder="{lang}wcf.acp.user.authentication.failure.time.start{/lang}">
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="date" id="endDate" name="filter[endDate]" value="{$filter[endDate]}" data-placeholder="{lang}wcf.acp.user.authentication.failure.time.end{/lang}">
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="userAgent" name="filter[userAgent]" value="{$filter[userAgent]}" placeholder="{lang}wcf.user.userAgent{/lang}" class="long">
				</dd>
			</dl>

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
			{pages print=true assign=pagesLinks controller='UserAuthenticationFailureList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$filterLinkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnFailureID{if $sortField == 'failureID'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=failureID&sortOrder={if $sortField == 'failureID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnEnvironment{if $sortField == 'environment'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=environment&sortOrder={if $sortField == 'environment' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.authentication.failure.environment{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.authentication.failure.time{/lang}</a></th>
					<th class="columnText columnValidationError{if $sortField === 'validationError'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=validationError&sortOrder={if $sortField === 'validationError' && $sortOrder === 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.user.authentication.failure.validationError{/lang}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}</a></th>
					<th class="columnText columnUserAgent{if $sortField == 'userAgent'} active {@$sortOrder}{/if}"><a href="{link controller='UserAuthenticationFailureList'}pageNo={@$pageNo}&sortField=userAgent&sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.userAgent{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item='authenticationFailure'}
					<tr>
						<td class="columnID columnFailureID">{@$authenticationFailure->failureID}</td>
						<td class="columnText columnEnvironment">{lang}wcf.acp.user.authentication.failure.environment.{@$authenticationFailure->environment}{/lang}</td>
						<td class="columnTitle columnUsername">
							{if $authenticationFailure->userID}
								<a href="{link controller='UserEdit' id=$authenticationFailure->userID}{/link}">{$authenticationFailure->username}</a>
							{else}
								{$authenticationFailure->username}
							{/if}
						</td>
						<td class="columnDate columnTime">{@$authenticationFailure->time|time}</td>
						<td class="columnSmallText columnValidationError">
							{if $authenticationFailure->validationError}
								{lang}wcf.acp.user.authentication.failure.validationError.{$authenticationFailure->validationError}{/lang}
							{/if}
						</td>
						<td class="columnSmallText columnIpAddress">{$authenticationFailure->getIpAddress()}</td>
						<td class="columnSmallText columnUserAgent" title="{$authenticationFailure->userAgent}">{$authenticationFailure->userAgent|truncate:75|tableWordwrap}</td>
						
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
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

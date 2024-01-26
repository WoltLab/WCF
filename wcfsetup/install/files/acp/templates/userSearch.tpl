{include file='header' pageTitle='wcf.acp.user.search'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], function(UiUserSearchInput) {
		new UiUserSearchInput(elBySel('input[name="username"]'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.user.search{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li class="dropdown">
				<a class="button dropdownToggle">{icon name='magnifying-glass'} <span>{lang}wcf.acp.user.quickSearch{/lang}</span></a>
				<ul class="dropdownMenu">
					<li><a href="{link controller='UserQuickSearch'}mode=banned{/link}">{lang}wcf.acp.user.quickSearch.banned{/lang}</a></li>
					<li><a href="{link controller='UserQuickSearch'}mode=newest{/link}">{lang}wcf.acp.user.quickSearch.newest{/lang}</a></li>
					<li><a href="{link controller='UserQuickSearch'}mode=disabled{/link}">{lang}wcf.acp.user.quickSearch.disabled{/lang}</a></li>
					<li><a href="{link controller='UserQuickSearch'}mode=pendingActivation{/link}">{lang}wcf.acp.user.quickSearch.pendingActivation{/lang}</a></li>
					<li><a href="{link controller='UserQuickSearch'}mode=disabledAvatars{/link}">{lang}wcf.acp.user.quickSearch.disabledAvatars{/lang}</a></li>
					<li><a href="{link controller='UserQuickSearch'}mode=disabledSignatures{/link}">{lang}wcf.acp.user.quickSearch.disabledSignatures{/lang}</a></li>
					
					{event name='quickSearchItems'}
				</ul>
			</li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $errorField == 'search'}
	<woltlab-core-notice type="error">{lang}wcf.acp.user.search.error.noMatches{/lang}</woltlab-core-notice>
{else}
	{include file='shared_formError'}
{/if}

<form method="post" action="{link controller='UserSearch'}{/link}">
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.user.search.conditions{/lang}</h2>
		</header>

		{include file='shared_userConditions'}
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.acp.user.search.display{/lang}</h2>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.user.search.display.general{/lang}</h2>
			
			<dl>
				<dt><label for="sortField">{lang}wcf.acp.user.search.display.sort{/lang}</label></dt>
				<dd>
					<select id="sortField" name="sortField">
						<option value="userID"{if $sortField == 'userID'} selected{/if}>{lang}wcf.user.userID{/lang}</option>
						<option value="username"{if $sortField == 'username'} selected{/if}>{lang}wcf.user.username{/lang}</option>
						<option value="email"{if $sortField == 'email'} selected{/if}>{lang}wcf.user.email{/lang}</option>
						<option value="registrationDate"{if $sortField == 'registrationDate'} selected{/if}>{lang}wcf.user.registrationDate{/lang}</option>
						
						{if $additionalSortFields|isset}{@$additionalSortFields}{/if}
					</select>
					
					<select id="sortOrder" name="sortOrder">
						<option value="ASC"{if $sortOrder == 'ASC'} selected{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
						<option value="DESC"{if $sortOrder == 'DESC'} selected{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="itemsPerPage">{lang}wcf.acp.user.search.display.itemsPerPage{/lang}</label></dt>
				<dd>
					<input type="number" id="itemsPerPage" name="itemsPerPage" value="{$itemsPerPage}" class="tiny">
				</dd>
			</dl>
			
			{event name='searchDisplayFields'}
		</section>
		
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.acp.user.search.display.columns{/lang}</h2>
			
			{if $columnOptions|count}
				<dl>
					<dt>
						<label>{lang}wcf.acp.user.search.display.columns.profile{/lang}</label>
					</dt>
					<dd>
						{foreach from=$columnOptions item=optionData}
							{assign var='option' value=$optionData.object}
							
							{* the 'about me' field does not qualify for display *}
							{if $option->optionName !== 'aboutMe'}
								<label><input type="checkbox" name="columns[]" value="{$option->optionName}"{if $option->optionName|in_array:$columns} checked{/if}> {$option->getTitle()}</label>
							{/if}
						{/foreach}
					</dd>
				</dl>
			{/if}
			
			<dl>
				<dt><label>{lang}wcf.acp.user.search.display.columns.other{/lang}</label></dt>
				<dd>
					{if $__wcf->session->getPermission('admin.user.canEditMailAddress')}
						<label><input type="checkbox" name="columns[]" value="email"{if "email"|in_array:$columns} checked{/if}> {lang}wcf.user.email{/lang}</label>
					{/if}
					<label><input type="checkbox" name="columns[]" value="registrationDate"{if "registrationDate"|in_array:$columns} checked{/if}> {lang}wcf.user.registrationDate{/lang}</label>
					<label><input type="checkbox" name="columns[]" value="lastActivityTime"{if "lastActivityTime"|in_array:$columns} checked{/if}> {lang}wcf.user.lastActivityTime{/lang}</label>
					<label><input type="checkbox" name="columns[]" value="profileHits"{if "profileHits"|in_array:$columns} checked{/if}> {lang}wcf.user.profileHits{/lang}</label>
					<label><input type="checkbox" name="columns[]" value="activityPoints"{if "activityPoints"|in_array:$columns} checked{/if}> {lang}wcf.user.activityPoint{/lang}</label>
					{if MODULE_LIKE}
						<label><input type="checkbox" name="columns[]" value="likesReceived"{if "likesReceived"|in_array:$columns} checked{/if}> {lang}wcf.like.likesReceived{/lang}</label>
					{/if}
					{event name='searchDisplayColumns'}
				</dd>
			</dl>
			
			{event name='searchDisplayColumnFields'}
		</section>
		
		{event name='resultOptionSections'}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}

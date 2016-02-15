{capture assign='__searchFormLink'}{link controller='Search'}{/link}{/capture}
{capture assign='__searchInputPlaceholder'}{lang}wcf.global.search.enterSearchTerm{/lang}{/capture}
{capture assign='__searchDropdownOptions'}<label><input type="checkbox" name="subjectOnly" value="1" /> {lang}wcf.search.subjectOnly{/lang}</label>{/capture}
{assign var='__searchHiddenInputFields' value=''}

{event name='settings'}

<div id="pageHeaderSearch" class="pageHeaderSearch">
	<form method="post" action="{@$__searchFormLink}">
		<div id="pageHeaderSearchInputContainer" class="pageHeaderSearchInputContainer dropdown" data-disable-auto-focus="true" data-dropdown-prevent-toggle="true">
			<input type="search" name="q" id="pageHeaderSearchInput" class="pageHeaderSearchInput dropdownToggle" placeholder="{@$__searchInputPlaceholder}" autocomplete="off" required="required" value="{if $query|isset}{$query}{/if}" data-toggle="search" />
			
			<ul class="dropdownMenu">
				{hascontent}
					<li class="dropdownText">
						{content}
							{@$__searchDropdownOptions}
						{/content}
					</li>
					<li class="dropdownDivider"></li>
				{/hascontent}
				<li><a href="{@$__searchFormLink}">{lang}wcf.search.extended{/lang}</a></li>
			</ul>
			
			<button class="pageHeaderSearchInputButton" type="submit">
				<span class="icon icon16 pointer fa-search" title="{lang}wcf.global.search{/lang}"></span>
			</button>
		</div>
		
		{@$__searchHiddenInputFields}
		{@SECURITY_TOKEN_INPUT_TAG}
	</form>
</div>

{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
	<script data-relocate="true">
		$(function() {
			new WCF.Search.Message.SearchArea($('#pageHeaderSearchInputContainer'));
		});
	</script>
{/if}
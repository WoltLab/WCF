{include file='documentHeader'}

<head>
	<title>{if $searchID}{lang}wcf.user.search.results{/lang}{else}{lang}wcf.user.members{/lang}{/if} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	{capture assign='canonicalURLParameters'}sortField={@$sortField}&sortOrder={@$sortOrder}{if $letter}&letter={@$letter|rawurlencode}{/if}{/capture}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='MembersList'}pageNo={@$pageNo+1}&{@$canonicalURLParameters}{/link}" />
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='MembersList'}{if $pageNo > 2}pageNo={@$pageNo-1}&{/if}{@$canonicalURLParameters}{/link}" />
	{/if}
	<link rel="canonical" href="{link controller='MembersList'}{if $pageNo > 1}pageNo={@$pageNo}&{/if}{@$canonicalURLParameters}{/link}" />
	
	<script data-relocate="true">
		//<![CDATA[
			$(function() {
				WCF.Language.addObject({
					'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
					'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
					'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
					'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
				});
				
				new WCF.User.Action.Follow($('.userList > li'));
				new WCF.User.Action.Ignore($('.userList > li'));
				
				new WCF.Search.User('#searchUsername', function(data) {
					var $link = '{link controller='User' id=2147483646 title='wcfTitlePlaceholder' encode=false}{/link}';
					window.location = $link.replace('2147483646', data.objectID).replace('wcfTitlePlaceholder', data.label);
				}, false, [ ], false);
			});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">

{capture assign='sidebar'}
	{assign var=encodedLetter value=$letter|rawurlencode}
	<div class="jsOnly">
		<form method="post" action="{link controller='UserSearch'}{/link}">
			<fieldset>
				<legend><label for="searchUsername">{lang}wcf.user.search{/lang}</label></legend>
				
				<dl>
					<dt></dt>
					<dd>
						<input type="text" id="searchUsername" name="username" class="long" placeholder="{lang}wcf.user.username{/lang}" />
						{@SECURITY_TOKEN_INPUT_TAG}
					</dd>
				</dl>
			</fieldset>
		</form>
	</div>
	
	<fieldset>
		<legend>{lang}wcf.user.members.sort.letters{/lang}</legend>
				
		<ul class="buttonList smallButtons letters">
			{foreach from=$letters item=__letter}
				<li><a href="{if $searchID}{link controller='MembersList' id=$searchID}sortField={$sortField}&sortOrder={$sortOrder}&letter={$__letter|rawurlencode}{/link}{else}{link controller='MembersList'}sortField={$sortField}&sortOrder={$sortOrder}&letter={$__letter|rawurlencode}{/link}{/if}" class="button small{if $letter == $__letter} active{/if}">{$__letter}</a></li>
			{/foreach}
			{if !$letter|empty}<li><a href="{if $searchID}{link controller='MembersList' id=$searchID}sortField={$sortField}&sortOrder={$sortOrder}{/link}{else}{link controller='MembersList'}sortField={$sortField}&sortOrder={$sortOrder}{/link}{/if}" class="button small">{lang}wcf.user.members.sort.letters.all{/lang}</a></li>{/if}
		</ul>
	</fieldset>
		
	<div>
		<form method="get" action="{if $searchID}{link controller='MembersList' id=$searchID}{/link}{else}{link controller='MembersList'}{/link}{/if}">
			<fieldset>
				<legend><label for="sortField">{lang}wcf.user.members.sort{/lang}</label></legend>
				
				<dl>
					<dt></dt>
					<dd>
						<select id="sortField" name="sortField">
							<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wcf.user.username{/lang}</option>
							<option value="registrationDate"{if $sortField == 'registrationDate'} selected="selected"{/if}>{lang}wcf.user.registrationDate{/lang}</option>
							<option value="activityPoints"{if $sortField == 'activityPoints'} selected="selected"{/if}>{lang}wcf.user.activityPoint{/lang}</option>
							<option value="likesReceived"{if $sortField == 'likesReceived'} selected="selected"{/if}>{lang}wcf.like.likesReceived{/lang}</option>
							{event name='sortField'}
						</select>
						<select name="sortOrder">
							<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
							<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
						</select>
					</dd>
				</dl>
			</fieldset>
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
				<input type="hidden" name="letter" value="{$letter}" />
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</form>
	</div>
	
	{@$__boxSidebar}
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
	<h1>{if $searchID}{lang}wcf.user.search.results{/lang}{else}{lang}wcf.user.members{/lang}{/if} <span class="badge">{#$items}</span></h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{if $searchID}
		{pages print=true assign=pagesLinks controller='MembersList' id=$searchID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&letter=$encodedLetter"}
	{else}
		{pages print=true assign=pagesLinks controller='MembersList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&letter=$encodedLetter"}
	{/if}
	
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

{if $items}
	<div class="container marginTop">
		<ol class="containerList doubleColumned userList">
			{foreach from=$objects item=user}
				{include file='userListItem'}
			{/foreach}
		</ol>
	</div>
{else}
	<p class="info">{lang}wcf.user.members.noMembers{/lang}</p>
{/if}

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

{include file='footer'}

</body>
</html>

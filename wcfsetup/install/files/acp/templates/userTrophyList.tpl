{include file='header' pageTitle='wcf.acp.menu.link.userTrophy.list'}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/User/Search/Input'], (UiUserSearchInput) => {
		new UiUserSearchInput(document.getElementById('username'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.userTrophy.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserTrophyAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.menu.link.userTrophy.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

<form method="post" action="{link controller='UserTrophyList'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-6">
				<dt></dt>
				<dd>
					<select name="trophyID" id="trophyID" class="long">
						<option value="0">{lang}wcf.global.noSelection{/lang}</option>
						
						{foreach from=$trophyCategories item=category}
							<optgroup label="{$category->getTitle()}">
								{foreach from=$category->getTrophies(true) item=trophy}
									<option value="{$trophy->trophyID}"{if $trophy->trophyID == $trophyID} selected{/if}>{$trophy->getTitle()}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-6">
				<dt></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" placeholder="{lang}wcf.user.username{/lang}" class="long">
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
			{assign var='linkParameters' value=''}
			{if $trophyID}{capture append=linkParameters}&trophyID={@$trophyID|rawurlencode}{/capture}{/if}
			{if $username}{capture append=linkParameters}&username={@$username|rawurlencode}{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller='UserTrophyList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\user\trophy\UserTrophyAction">
			<thead>
			<tr>
				<th class="columnID columnUserTrophyID{if $sortField == 'userTrophyID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserTrophyList'}pageNo={@$pageNo}&sortField=userTrophyID&sortOrder={if $sortField == 'userTrophyID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
				<th class="columnText columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='UserTrophyList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.user.username{/lang}</a></th>
				<th class="columnTitle columnTrophy{if $sortField == 'trophyID'} active {@$sortOrder}{/if}"><a href="{link controller='UserTrophyList'}pageNo={@$pageNo}&sortField=trophyID&sortOrder={if $sortField == 'trophyID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.acp.trophy{/lang}</a></th>
				<th class="columnDate columnUserTrophyTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='UserTrophyList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.date{/lang}</a></th>

				{event name='columnHeads'}
			</tr>
			</thead>

			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=userTrophy}
					<tr class="userTrophyRow jsObjectActionObject" data-object-id="{@$userTrophy->getObjectID()}">
						<td class="columnIcon">
							{if $userTrophy->getTrophy()->awardAutomatically}
								<span class="disabled" title="{lang}wcf.global.button.edit{/lang}">
									{icon name='pencil'}
								</span>
								<span class="disabled" title="{lang}wcf.global.button.delete{/lang}">
									{icon name='xmark'}
								</span>
							{else}
								<a href="{link controller='UserTrophyEdit' id=$userTrophy->userTrophyID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip{if $userTrophy->getTrophy()->awardAutomatically} disabled{/if}">
									{icon name='pencil'}
								</a>
								{objectAction action="delete" confirmMessage='wcf.acp.trophy.userTrophy.delete.confirmMessage'}
							{/if}
						</td>
						<td class="columnID columnUserTrophyID">{@$userTrophy->userTrophyID}</td>
						<td class="columnText columnUsername"><a href="{link controller='UserEdit' id=$userTrophy->userID}{/link}" title="{lang}wcf.acp.user.edit{/lang}">{$userTrophy->getUserProfile()->username}</a></td>
						<td class="columnTitle columnTrophy">{$userTrophy->getTrophy()->getTitle()}</td>
						<td class="columnDate columnUserTrophyTime">{@$userTrophy->time|time}</td>
						
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
				<li><a href="{link controller='UserTrophyAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.menu.link.userTrophy.add{/lang}</span></a></li>

				{event name='contentHeaderNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

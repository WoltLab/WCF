{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var actionObjects = { };
		actionObjects['com.woltlab.wcf.user'] = { };
		actionObjects['com.woltlab.wcf.user']['delete'] = new WCF.Action.Delete('wcf\\data\\user\\UserAction', $('.jsUserRow'), $('#userTableContainer .wcf-menu li:first-child .wcf-badge'));
					
		WCF.Clipboard.init('wcf\\acp\\page\\UserListPage', {@$hasMarkedItems}, actionObjects);
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#userTableContainer'), 'jsUserRow', options);
	});
	//]]>
</script>

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/{if $searchID}search{else}user{/if}1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.user.{if $searchID}search{else}list{/if}{/lang}</h1>
		<h2>{if $searchID}{lang}wcf.acp.user.search.matches{/lang}{/if}</h2><!-- ToDo: deprecated display -->
	</hgroup>
</header>

{assign var=encodedURL value=$url|rawurlencode}
{assign var=encodedAction value=$action|rawurlencode}
<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller="UserList" link="pageNo=%d&searchID=$searchID&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul class="wcf-largeButtons">
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="{link controller='UserAdd'}{/link}" title="{lang}wcf.acp.user.add{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

<div id="userTableContainer" class="wcf-box wcf-boxTitle wcf-marginTop wcf-shadow1">
	<nav class="wcf-menu">
		<ul>
			<li{if $action == ''} class="active"{/if}><a href="{link controller='UserList'}{/link}"><span>{lang}wcf.acp.user.list.all{/lang}</span> <span class="wcf-badge" title="{lang}wcf.acp.user.list.count{/lang}">{#$items}</span></a></li>
			
			{event name='userListOptions'}
		</ul>
	</nav>
	
	{hascontent}
		<table data-type="com.woltlab.wcf.user" class="wcf-table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll" /></label></th>
					<th class="columnID columnUserID{if $sortField == 'userID'} active{/if}" colspan="2"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'userID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active{/if}"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $sortField == $column} active{/if}"><a href="{link controller='UserList'}searchID={@$searchID}&action={@$encodedAction}&pageNo={@$pageNo}&sortField={$column}&sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}{$columnLanguageVariable}{/lang}{if $sortField == $column} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					{/foreach}
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$users item=user}
						<tr class="jsUserRow">
							<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$user->userID}" /></td>
							<td class="columnIcon">
								{if $user->editable}
									<a href="{link controller='UserEdit' id=$user->userID}{/link}"><img src="{@$__wcf->getPath()}icon/edit1.svg" alt="" title="{lang}wcf.acp.user.edit{/lang}" class="jsTooltip" /></a>
								{else}
									<img src="{@$__wcf->getPath()}icon/edit1D.svg" alt="" title="{lang}wcf.acp.user.edit{/lang}" />
								{/if}
								{if $user->deletable}
									<img src="{@$__wcf->getPath()}icon/delete1.svg" alt="" title="{lang}wcf.acp.user.delete{/lang}" class="jsTooltip jsDeleteButton" data-object-id="{@$user->userID}" data-confirm-message="{lang}wcf.acp.user.delete.sure{/lang}" />
								{else}
									<img src="{@$__wcf->getPath()}icon/delete1D.svg" alt="" title="{lang}wcf.acp.user.delete{/lang}" />
								{/if}
						
								{event name='buttons'}
							</td>
							<td class="columnID columnUserID"><p>{@$user->userID}</p></td>
							<td class="columnTitle columnUsername"><p>{if $user->editable}<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>{else}{$user->username}{/if}</p></td>
					
							{foreach from=$columnHeads key=column item=columnLanguageVariable}
								<td class="column{$column|ucfirst}"><p>{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</p></td>
							{/foreach}
					
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="wcf-contentFooter">
		{@$pagesLinks}
		
		<div class="wcf-clipboardEditor jsClipboardEditor" data-types="[ 'com.woltlab.wcf.user' ]"></div>
		
		<nav>
			<ul class="wcf-largeButtons">
				{if $__wcf->session->getPermission('admin.user.canAddUser')}
					<li><a href="{link controller='UserAdd'}{/link}" title="{lang}wcf.acp.user.add{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
				{/if}
				<li><a href="{link controller='UserSearch'}{/link}" title="{lang}wcf.acp.user.search{/lang}" class="wcf-button"><img src="{@$__wcf->getPath()}icon/search1.svg" alt="" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	</div>
{hascontentelse}
</div>

<p class="wcf-info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/hascontent}

{include file='footer'}

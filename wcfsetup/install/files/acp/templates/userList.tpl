{if $searchID}
	{assign var='pageTitle' value='wcf.acp.user.search'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.list'}
{/if}

{include file='header'}

{event name='javascriptInclude'}
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		var actionObjects = { };
		actionObjects['com.woltlab.wcf.user'] = { };
		actionObjects['com.woltlab.wcf.user']['delete'] = new WCF.Action.Delete('wcf\\data\\user\\UserAction', '.jsUserRow');
		
		WCF.Clipboard.init('wcf\\acp\\page\\UserListPage', {@$hasMarkedItems}, actionObjects);
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#userTableContainer'), 'jsUserRow', options);
		
		WCF.Language.addObject({
			'wcf.acp.user.banReason': '{lang}wcf.acp.user.banReason{/lang}',
			'wcf.acp.user.banReason.description': '{lang}wcf.acp.user.banReason.description{/lang}',
			'wcf.acp.user.ban.sure': '{lang}wcf.acp.user.ban.sure{/lang}',
			'wcf.acp.user.ban.expires': '{lang}wcf.acp.user.ban.expires{/lang}',
			'wcf.acp.user.ban.expires.description': '{lang}wcf.acp.user.ban.expires.description{/lang}',
			'wcf.acp.user.ban.neverExpires': '{lang}wcf.acp.user.ban.neverExpires{/lang}',
			'wcf.acp.user.sendNewPassword.workerTitle': '{lang}wcf.acp.user.sendNewPassword.workerTitle{/lang}',
			'wcf.acp.worker.abort.confirmMessage': '{lang}wcf.acp.worker.abort.confirmMessage{/lang}'
		});
		WCF.ACP.User.BanHandler.init();
		
		{if $__wcf->session->getPermission('admin.user.canEnableUser')}
			WCF.ACP.User.EnableHandler.init();
		{/if}
		
		{if $__wcf->session->getPermission('admin.user.canEditPassword')}
			WCF.ACP.User.SendNewPasswordHandler.init();
		{/if}
		
		{event name='javascriptInit'}
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}{@$pageTitle}{/lang}</h1>
</header>

{assign var=encodedURL value=$url|rawurlencode}
{assign var=encodedAction value=$action|rawurlencode}
<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="UserList" id=$searchID link="pageNo=%d&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			{if $__wcf->session->getPermission('admin.user.canAddUser')}
				<li><a href="{link controller='UserAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
			{/if}
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $users|count}
	<div id="userTableContainer" class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.user.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table data-type="com.woltlab.wcf.user" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll" /></label></th>
					<th class="columnID columnUserID{if $sortField == 'userID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $sortField == $column} active {@$sortOrder}{/if}"><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField={$column}&sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}{$columnLanguageVariable}{/lang}</a></th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$users item=user}
					<tr class="jsUserRow jsClipboardObject">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$user->userID}" /></td>
						<td class="columnIcon">
							{if $user->editable}
								<a href="{link controller='UserEdit' id=$user->userID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							{else}
								<span class="icon icon16 icon-pencil disabled" title="{lang}wcf.global.button.edit{/lang}"></span>
							{/if}
							{if $user->deletable}
								<span class="icon icon16 icon-remove jsTooltip jsDeleteButton pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$user->userID}" data-confirm-message="{lang}wcf.acp.user.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							{if $user->bannable}
								<span class="icon icon16 icon-{if $user->banned}lock{else}unlock{/if} jsBanButton jsTooltip pointer" title="{lang}wcf.acp.user.{if $user->banned}unban{else}ban{/if}{/lang}" data-object-id="{@$user->userID}" data-ban-message="{lang}wcf.acp.user.ban{/lang}" data-unban-message="{lang}wcf.acp.user.unban{/lang}" data-banned="{if $user->banned}true{else}false{/if}"></span>
							{else}
								<span class="icon icon16 icon-{if $user->banned}lock{else}unlock{/if} disabled" title="{lang}wcf.acp.user.{if $user->banned}unban{else}ban{/if}{/lang}"></span>
							{/if}
							{if $user->canBeEnabled}
								<span class="icon icon16 icon-{if !$user->activationCode}check{else}check-empty{/if} jsEnableButton jsTooltip pointer" title="{lang}wcf.acp.user.{if !$user->activationCode}disable{else}enable{/if}{/lang}" data-object-id="{@$user->userID}" data-enable-message="{lang}wcf.acp.user.enable{/lang}" data-disable-message="{lang}wcf.acp.user.disable{/lang}" data-enabled="{if !$user->activationCode}true{else}false{/if}"></span>
							{else}
								<span class="icon icon16 icon-{if !$user->activationCode}check{else}check-empty{/if} disabled" title="{lang}wcf.acp.user.{if !$user->activationCode}disable{else}enable{/if}{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnUserID">{@$user->userID}</td>
						<td class="columnIcon"><p class="framed">{@$user->getAvatar()->getImageTag(24)}</p></td>
						<td class="columnTitle columnUsername">{if $user->editable}<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>{else}{$user->username}{/if}{if MODULE_USER_RANK}{if $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}{if $user->getRank() && $user->getRank()->rankImage} <span class="userRankImage">{@$user->getRank()->getImage()}</span>{/if}{/if}</td>
						
						{foreach from=$columnHeads key=column item=columnLanguageVariable}
							<td class="column{$column|ucfirst}{if $columnStyling[$column]|isset} {$columnStyling[$column]}{/if}">{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</td>
						{/foreach}
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
		
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				{if $__wcf->session->getPermission('admin.user.canAddUser')}
					<li><a href="{link controller='UserAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
				{/if}
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
		
		<nav class="jsClipboardEditor" data-types="[ 'com.woltlab.wcf.user' ]"></nav>
	</div>
{else}
	<p class="info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/if}

{include file='footer'}

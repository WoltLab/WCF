{if $searchID}
	{assign var='pageTitle' value='wcf.acp.user.search'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.list'}
{/if}

{include file='header'}

{event name='javascriptInclude'}
<script data-relocate="true">
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
		
		require(['Language', 'WoltLabSuite/Core/Acp/Ui/User/Editor'], function (Language, AcpUiUserList) {
			Language.addObject({
				'wcf.acp.user.action.sendNewPassword.confirmMessage': '{lang}wcf.acp.user.action.sendNewPassword.confirmMessage{/lang}'
			});
			
			AcpUiUserList.init();
		});
		
		{event name='javascriptInit'}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang} {if $items}<span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.user.canAddUser')}
						<li><a href="{link controller='UserAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentHeaderNavigation'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var=encodedURL value=$url|rawurlencode}
			{assign var=encodedAction value=$action|rawurlencode}
			
			{pages print=true assign=pagesLinks controller="UserList" id=$searchID link="pageNo=%d&action=$encodedAction&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $users|count}
	<div id="userTableContainer" class="section tabularBox">
		<table data-type="com.woltlab.wcf.user" class="table jsClipboardContainer">
			<thead>
				<tr>
					<th class="columnMark"><label><input type="checkbox" class="jsClipboardMarkAll"></label></th>
					<th class="columnID columnUserID{if $sortField == 'userID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					
					{foreach from=$columnHeads key=column item=columnLanguageVariable}
						<th class="column{$column|ucfirst}{if $columnStyling[$column]|isset} {$columnStyling[$column]}{/if}{if $sortField == $column} active {@$sortOrder}{/if}"{if $column === 'registrationDate'} colspan="2"{/if}><a href="{link controller='UserList' id=$searchID}action={@$encodedAction}&pageNo={@$pageNo}&sortField={$column}&sortOrder={if $sortField == $column && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}{$columnLanguageVariable}{/lang}</a></th>
					{/foreach}
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$users item=user}
					<tr class="jsUserRow jsClipboardObject" data-object-id="{@$user->userID}" data-banned="{if $user->banned}true{else}false{/if}" data-enabled="{if !$user->activationCode}true{else}false{/if}">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$user->userID}"></td>
						<td class="columnIcon">
							<div class="dropdown" id="userListDropdown{@$user->userID}">
								<a href="#" class="dropdownToggle button small"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a>
								
								<ul class="dropdownMenu">
									{event name='dropdownItems'}
									
									{if $user->userID !== $__wcf->user->userID}
										<li><a href="{link controller='UserMail' id=$user->userID}{/link}">{lang}wcf.acp.user.action.sendMail{/lang}</a></li>
										
										{if $user->accessible && $__wcf->session->getPermission('admin.user.canEditPassword')}
											<li><a href="#" class="jsSendNewPassword">{lang}wcf.acp.user.action.sendNewPassword{/lang}</a></li>
										{/if}
									{/if}
									
									{if $user->deletable}
										<li class="dropdownDivider"></li>
										<li><a href="#" class="jsDispatchDelete">{lang}wcf.global.button.delete{/lang}</a></li>
									{/if}
									
									{if $user->editable}
										<li class="dropdownDivider"></li>
										<li><a href="{link controller='UserEdit' id=$user->userID}{/link}" class="jsEditLink">{lang}wcf.global.button.edit{/lang}</a></li>
									{/if}
								</ul>
							</div>
							
							<div class="jsLegacyButtons" style="display: none">
								{* The old buttons (with the exception of the edit button) should remain here
								   for backwards-compatibility, they're sometimes referenced with JavaScript-
								   based insert calls. Clicks are forwarded to them anyway, thus there is no
								   significant downside, other than "just" some more legacy code. *}
								
								{if $user->deletable}
									<span class="icon icon16 fa-times jsTooltip jsDeleteButton pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$user->userID}" data-confirm-message-html="{lang __encode=true}wcf.acp.user.delete.sure{/lang}"></span>
								{/if}
								{if $user->bannable}
									<span class="icon icon16 fa-{if $user->banned}lock{else}unlock{/if} jsBanButton jsTooltip pointer" title="{lang}wcf.acp.user.{if $user->banned}unban{else}ban{/if}{/lang}" data-object-id="{@$user->userID}" data-ban-message="{lang}wcf.acp.user.ban{/lang}" data-unban-message="{lang}wcf.acp.user.unban{/lang}" data-banned="{if $user->banned}true{else}false{/if}"></span>
								{/if}
								{if $user->canBeEnabled}
									<span class="icon icon16 fa-{if !$user->activationCode}check-square-o{else}square-o{/if} jsEnableButton jsTooltip pointer" title="{lang}wcf.acp.user.{if !$user->activationCode}disable{else}enable{/if}{/lang}" data-object-id="{@$user->userID}" data-enable-message="{lang}wcf.acp.user.enable{/lang}" data-disable-message="{lang}wcf.acp.user.disable{/lang}" data-enabled="{if !$user->activationCode}true{else}false{/if}"></span>
								{/if}
								
								{event name='rowButtons'}
							</div>
						</td>
						<td class="columnID columnUserID">{@$user->userID}</td>
						<td class="columnIcon">{@$user->getAvatar()->getImageTag(24)}</td>
						<td class="columnTitle columnUsername">
							{if $user->editable}
								<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>
							{else}
								{$user->username}
							{/if}
							{if MODULE_USER_RANK}
								{if $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}
								{if $user->getRank() && $user->getRank()->rankImage} <span class="userRankImage">{@$user->getRank()->getImage()}</span>{/if}
							{/if}
						</td>
						
						{foreach from=$columnHeads key=column item=columnLanguageVariable}
							{if $column === 'registrationDate'}
								<td class="columnDate columnRegistrationIpAddress">
									{if $__wcf->session->getPermission('admin.user.canViewIpAddress') && $user->registrationIpAddress}
										<span class="jsTooltip" title="{lang}wcf.user.registrationIpAddress{/lang}">{$user->getRegistrationIpAddress()}</span>
									{/if}
								</td>
							{/if}
							<td class="column{$column|ucfirst}{if $columnStyling[$column]|isset} {$columnStyling[$column]}{/if}">{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</td>
						{/foreach}
						
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
						{if $__wcf->session->getPermission('admin.user.canAddUser')}
							<li><a href="{link controller='UserAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
						{/if}
						
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.acp.user.search.error.noMatches{/lang}</p>
{/if}

{include file='footer'}

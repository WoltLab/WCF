{if $searchID}
	{assign var='pageTitle' value='wcf.acp.user.search'}
{else}
	{assign var='pageTitle' value='wcf.acp.user.list'}
{/if}

{include file='header'}

{event name='javascriptInclude'}
<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/Clipboard'], (ControllerClipboard) => {
		ControllerClipboard.setup({
			pageClassName: 'wcf\\acp\\page\\UserListPage',
			hasMarkedItems: {if $hasMarkedItems}true{else}false{/if},
		});
	});
	
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.user.banReason': '{jslang}wcf.acp.user.banReason{/jslang}',
			'wcf.acp.user.banReason.description': '{jslang}wcf.acp.user.banReason.description{/jslang}',
			'wcf.acp.user.ban.sure': '{jslang}wcf.acp.user.ban.sure{/jslang}',
			'wcf.acp.user.ban.expires': '{jslang}wcf.acp.user.ban.expires{/jslang}',
			'wcf.acp.user.ban.expires.description': '{jslang}wcf.acp.user.ban.expires.description{/jslang}',
			'wcf.acp.user.ban.neverExpires': '{jslang}wcf.acp.user.ban.neverExpires{/jslang}',
			'wcf.acp.user.sendNewPassword.workerTitle': '{jslang}wcf.acp.user.sendNewPassword.workerTitle{/jslang}',
			'wcf.acp.worker.abort.confirmMessage': '{jslang}wcf.acp.worker.abort.confirmMessage{/jslang}',
			'wcf.acp.content.removeContent': '{jslang}wcf.acp.content.removeContent{/jslang}',
			'wcf.user.status.banned': '{jslang}wcf.user.status.banned{/jslang}',
			'wcf.user.status.isDisabled': '{jslang}wcf.user.status.isDisabled{/jslang}'
		});
		WCF.ACP.User.BanHandler.init();

		{if $__wcf->session->getPermission('admin.user.canEnableUser')}
			WCF.ACP.User.EnableHandler.init();
		{/if}

		{if $__wcf->session->getPermission('admin.user.canEditPassword')}
			WCF.ACP.User.SendNewPasswordHandler.init();
		{/if}

		require(['Language', 'WoltLabSuite/Core/Acp/Ui/User/Editor', 'WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Clipboard'], function (Language, AcpUiUserList, { AcpUserContentRemoveClipboard }) {
			Language.addObject({
				'wcf.acp.user.action.sendNewPassword.confirmMessage': '{jslang}wcf.acp.user.action.sendNewPassword.confirmMessage{/jslang}',
			});

			new AcpUiUserList();

			new AcpUserContentRemoveClipboard();
		});

		{event name='javascriptInit'}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}{@$pageTitle}{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>

	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.user.canSearchUser')}
						<li class="dropdown">
							<a class="button dropdownToggle">{icon name='magnifying-glass'} <span>{lang}wcf.acp.user.quickSearch{/lang}</span></a>
							<ul class="dropdownMenu">
								<li><a href="{link controller='UserQuickSearch'}mode=banned{/link}">{lang}wcf.acp.user.quickSearch.banned{/lang}</a></li>
								<li><a href="{link controller='UserQuickSearch'}mode=newest{/link}">{lang}wcf.acp.user.quickSearch.newest{/lang}</a></li>
								<li><a href="{link controller='UserQuickSearch'}mode=disabled{/link}">{lang}wcf.acp.user.quickSearch.disabled{/lang}</a></li>
								<li><a href="{link controller='UserQuickSearch'}mode=pendingActivation{/link}">{lang}wcf.acp.user.quickSearch.pendingActivation{/lang}</a></li>
								<li><a href="{link controller='UserQuickSearch'}mode=disabledAvatars{/link}">{lang}wcf.acp.user.quickSearch.disabledAvatars{/lang}</a></li>
								<li><a href="{link controller='UserQuickSearch'}mode=disabledSignatures{/link}">{lang}wcf.acp.user.quickSearch.disabledSignatures{/lang}</a></li>
							</ul>
						</li>
					{/if}

					{if $__wcf->session->getPermission('admin.user.canAddUser')}
						<li><a href="{link controller='UserAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
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
		<table data-type="com.woltlab.wcf.user" class="table jsClipboardContainer jsObjectActionContainer" data-object-action-class-name="wcf\data\user\UserAction">
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

			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$users item=user}
					<tr class="jsUserRow jsClipboardObject jsObjectActionObject" data-object-id="{@$user->getObjectID()}" data-banned="{if $user->banned}true{else}false{/if}" data-enabled="{if !$user->activationCode}true{else}false{/if}" data-email-confirmed="{if $user->isEmailConfirmed()}true{else}false{/if}">
						<td class="columnMark"><input type="checkbox" class="jsClipboardItem" data-object-id="{@$user->userID}"></td>
						<td class="columnIcon">
							<div class="dropdown" id="userListDropdown{@$user->userID}">
								<a href="#" class="dropdownToggle button small">{icon name='pencil'} <span>{lang}wcf.global.button.edit{/lang}</span></a>

								<ul class="dropdownMenu">
									{event name='dropdownItems'}

									{if $user->userID !== $__wcf->user->userID}
										{if $user->accessible && $__wcf->session->getPermission('admin.user.canEnableUser')}
											<li><a href="#" class="jsEnable" data-enable-message="{lang}wcf.acp.user.enable{/lang}" data-disable-message="{lang}wcf.acp.user.disable{/lang}">{lang}wcf.acp.user.{if !$user->activationCode}disable{else}enable{/if}{/lang}</a></li>
										{/if}

										{if $user->accessible && $__wcf->session->getPermission('admin.user.canEnableUser')}
											<li><a href="#" class="jsConfirmEmailToggle" data-confirm-email-message="{lang}wcf.acp.user.action.confirmEmail{/lang}" data-unconfirm-email-message="{lang}wcf.acp.user.action.unconfirmEmail{/lang}">{lang}wcf.acp.user.action.{if $user->isEmailConfirmed()}un{/if}confirmEmail{/lang}</a></li>
										{/if}

										{if $user->bannable}
											<li><a href="#" class="jsBan" data-ban-message="{lang}wcf.acp.user.ban{/lang}" data-unban-message="{lang}wcf.acp.user.unban{/lang}">{lang}wcf.acp.user.{if $user->banned}unban{else}ban{/if}{/lang}</a></li>
										{/if}

										{if $__wcf->session->getPermission('admin.user.canMailUser')}
											<li><a href="{link controller='UserMail' id=$user->userID}{/link}">{lang}wcf.acp.user.action.sendMail{/lang}</a></li>
										{/if}

										{if $user->accessible && $__wcf->session->getPermission('admin.user.canEditPassword')}
											<li><a href="#" class="jsSendNewPassword">{lang}wcf.acp.user.action.sendNewPassword{/lang}</a></li>
										{/if}
									{/if}

									{if $user->accessible && $__wcf->session->getPermission('admin.user.canExportGdprData')}
										<li><a href="{link controller='UserExportGdpr' id=$user->userID}{/link}">{lang}wcf.acp.user.exportGdpr{/lang}</a></li>
									{/if}

									{if $user->deletable}
										<li class="dropdownDivider"></li>
										<li><a href="#" class="jsDelete" data-confirm-message="{lang __encode=true objectTitle=$user->username}wcf.button.delete.confirmMessage{/lang}">{lang}wcf.global.button.delete{/lang}</a></li>
										<li><a href="#" class="jsDeleteContent">{lang}wcf.acp.content.removeContent{/lang}</a></li>
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

								{event name='rowButtons'}
							</div>
						</td>
						<td class="columnID columnUserID">{@$user->userID}</td>
						<td class="columnIcon">{@$user->getAvatar()->getImageTag(24)}</td>
						<td class="columnTitle columnUsername">
							<span class="username">
								{if $user->editable}
									<a title="{lang}wcf.acp.user.edit{/lang}" href="{link controller='UserEdit' id=$user->userID}{/link}">{$user->username}</a>
								{else}
									{$user->username}
								{/if}
							</span>

							<span class="userStatusIcons">
								{if $user->banned}
									<span class="jsTooltip jsUserStatusBanned" title="{lang}wcf.user.status.banned{/lang}">
										{icon name='lock'}
									</span>
								{/if}
								{if $user->activationCode != 0}
									<span class="jsTooltip jsUserStatusIsDisabled" title="{lang}wcf.user.status.isDisabled{/lang}">
										{icon name='power-off'}
									</span>
									{if !$user->getBlacklistMatches()|empty}
										<span class="jsTooltip jsUserStatusBlacklistMatches" title="{lang}wcf.user.status.blacklistMatches{/lang}">
											{icon name='warning'}
										</span>
									{/if}
								{/if}
							</span>

							{if MODULE_USER_RANK}
								{if $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}
								{if $user->getRank() && $user->getRank()->rankImage} <span class="userRankImage">{@$user->getRank()->getImage()}</span>{/if}
							{/if}
						</td>

						{foreach from=$columnHeads key=column item=columnLanguageVariable}
							{if $column === 'lastActivityTime'}
								<td class="column{$column|ucfirst}{if $columnStyling[$column]|isset} {$columnStyling[$column]}{/if}">
									{if $user->isOnline()}
										{lang}wcf.user.online{/lang}
										<span class="userOnlineIndicator" aria-hidden="true"></span>
									{elseif $user->lastActivityTime}
										{time time=$user->lastActivityTime}
									{/if}
								</td>
							{else}
								{if $column === 'registrationDate'}
									<td class="columnDate columnRegistrationIpAddress">
										{if $__wcf->session->getPermission('admin.user.canViewIpAddress') && $user->registrationIpAddress}
											<span class="jsTooltip" title="{lang}wcf.user.registrationIpAddress{/lang}">{$user->getRegistrationIpAddress()}</span>
										{/if}
									</td>
								{/if}
								<td class="column{$column|ucfirst}{if $columnStyling[$column]|isset} {$columnStyling[$column]}{/if}">{if $columnValues[$user->userID][$column]|isset}{@$columnValues[$user->userID][$column]}{/if}</td>
							{/if}	
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
							<li><a href="{link controller='UserAdd'}{/link}" class="button">{icon name='plus'} <span>{lang}wcf.acp.user.add{/lang}</span></a></li>
						{/if}

						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}

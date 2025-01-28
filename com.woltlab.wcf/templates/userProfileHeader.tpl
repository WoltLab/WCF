<header
	class="userProfileHeader"
	data-object-id="{$view->user->userID}"
	{if $view->isInAccessibleGroup()}
		{if $__wcf->session->getPermission('admin.user.canBanUser')}
			data-banned="{$view->user->banned}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}
			data-disable-avatar="{$view->user->disableAvatar}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableSignature')}
			data-disable-signature="{$view->user->disableSignature}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableCoverPhoto')}
			data-disable-cover-photo="{$view->user->disableCoverPhoto}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canEnableUser')}
			data-is-disabled="{if $view->user->activationCode}true{else}false{/if}"
		{/if}
	{/if}
>
	<div class="userProfileHeader__coverPhotoContainer">
		<div class="userProfileHeader__coverPhoto">
			<img src="{$view->user->getCoverPhoto()->getURL()}" data-object-id="{$view->user->getCoverPhoto()->getObjectID()}" class="userProfileHeader__coverPhotoImage">
		</div>
		
		<div class="userProfileHeader__manageButtons">
			{event name='beforeManageButtons'}
			
			{if $view->canEditCoverPhoto()}
				<ul class="userProfileManageCoverPhoto buttonGroup buttonList smallButtons">
					<li>
						<button type="button" data-edit-cover-photo="{link controller="UserCoverPhoto" id=$user->userID}{/link}" data-default-cover-photo="{$__wcf->styleHandler->getStyle()->getCoverPhotoUrl()}" class="button small">
							{icon name='camera'} {lang}wcf.user.coverPhoto.management{/lang}
						</button>
					</li>
				</ul>
			{/if}

			{if $view->user->canEditAvatar()}
				<button type="button" data-edit-avatar="{link controller="UserAvatar" id=$view->user->userID}{/link}" class="button small">
					{icon name='circle-user' type='solid'} {lang}wcf.user.avatar.edit{/lang}
				</button>
			{/if}

			{if $view->canEditUser()}
				<button type="button" class="jsButtonEditProfile button small">{icon name='pencil'} <span>{lang}wcf.user.editProfile{/lang}</span></button>
			{/if}

			{event name='afterManageButtons'}
		</div>
	</div>

	<div class="userProfileHeader__content">
		<div class="userProfileHeader__avatar">
			<div class="userProfileHeader__avatarBorder">
				{unsafe:$view->user->getAvatar()->getImageTag(128)}
				
				{if $view->user->isOnline()}<span class="userProfileHeader__onlineIndicator jsTooltip" title="{lang username=$view->user->username}wcf.user.online.title{/lang}"></span>{/if}
			</div>
		</div>
		<div class="userProfileHeader__title">
			<h1 class="userProfileHeader__username">
				<span class="userProfileUsername">{$view->user->username}</span>
				{if $view->user->banned}
					<span class="jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}">
						{icon name='lock'}
					</span>
				{/if}
			</h1>
			<div class="userProfileHeader__rank">
				{if MODULE_USER_RANK}
					{if $view->user->getUserTitle()}
						<span class="badge userTitleBadge{if $view->user->getRank() && $view->user->getRank()->cssClassName} {$view->user->getRank()->cssClassName}{/if}">{$view->user->getUserTitle()}</span>
					{/if}
					{if $view->user->getRank() && $view->user->getRank()->rankImage}
						<span class="userRankImage">{unsafe:$view->user->getRank()->getImage()}</span>
					{/if}
				{/if}
			</div>
			{event name='afterTitle'}
		</div>
		<div class="userProfileHeader__stats">
			{foreach from=$view->getStatItems() item='statItem'}
				{if $statItem->link}
					<a href="{$statItem->link}" class="userProfileHeader__statItem {$statItem->cssClassName}" {unsafe:$statItem->attributes}>
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</a>
				{elseif $statItem->isButton}
					<button type="button" class="userProfileHeader__statItem {$statItem->cssClassName}" {unsafe:$statItem->attributes}>
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</button>
				{else}
					<div class="userProfileHeader__statItem {$statItem->cssClassName}">
						<span class="userProfileHeader__statTitle">{unsafe:$statItem->title}</span>
						<span class="userProfileHeader__statValue">{unsafe:$statItem->value}</span>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="userProfileHeader__buttons">
			{event name='beforeButtons'}
			
			{if $view->hasInteractionOptions()}
				<div class="userProfileHeader__button dropdown">
					<button type="button" class="button dropdownToggle" aria-label="{lang}wcf.global.button.more{/lang}">{icon name='ellipsis-vertical'}</button>

					<ul class="dropdownMenu">
						{foreach from=$view->getInteractionOptions() item='interactionOption'}
							<li>
								{if $interactionOption->link}
									<a href="{$interactionOption->link}" {unsafe:$interactionOption->attributes}>{$interactionOption->title}</a>
								{else}
									<button type="button" {unsafe:$interactionOption->attributes}>{$interactionOption->title}</a>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}

			{if $view->hasManagementOptions()}
				<div class="userProfileHeader__button dropdown">
					<button type="button" class="button dropdownToggle jsTooltip" title="{lang}wcf.user.profile.management{/lang}">{icon name='gear'}</button>

					<ul class="dropdownMenu userProfileHeader__managementOptions">
						{foreach from=$view->getManagementOptions() item='managementOption'}
							<li>
								{if $managementOption->link}
									<a href="{$managementOption->link}" {unsafe:$managementOption->attributes}>{$managementOption->title}</a>
								{else}
									<button type="button" {unsafe:$managementOption->attributes}>{$managementOption->title}</a>
								{/if}
							</li>
						{/foreach}
					</ul>
				</div>
			{/if}
			
			{if $view->hasSearchContentLinks()}
				<div class="userProfileHeader__button dropdown">
					<button type="button" class="button buttonPrimary dropdownToggle">
						{icon name='magnifying-glass'}
						<span>{lang}wcf.user.searchUserContent{/lang}</span>
					</button>
					<ul class="dropdownMenu">
						{foreach from=$view->getSearchContentLinks() item='searchContentLink'}
							<li><a href="{$searchContentLink->link}">{$searchContentLink->title}</a></li>
						{/foreach}
					</ul>
				</div>
			{/if}

			{event name='afterButtons'}
		</div>

		<div class="userProfileHeader__meta">
			<dl class="plain userProfileHeader__meta__dataList">
				{event name='beforeMeta'}

				<dt>{icon name='calendar'} {lang}wcf.user.registrationDate{/lang}</dt>
				<dd>{time time=$view->user->registrationDate type='plainDate'}</dd>
				{if $view->user->getOldUsername()}
					<dt>{icon name='scroll'} {lang}wcf.user.oldUsername{/lang}</dt>
					<dd>{$view->user->getOldUsername()}</dd>
				{/if}
				{if $view->user->canViewOnlineStatus() && $view->user->getLastActivityTime()}
					<dt>{icon name='clock'} {lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
					<dd>{time time=$view->user->getLastActivityTime()}</dd>
					{if $user->getCurrentLocation()}
						<dt>{icon name='location-arrow'} {lang}wcf.user.usersOnline.location{/lang}</dt>
						<dd>{unsafe:$user->getCurrentLocation()}</dd>
					{/if}
				{/if}
				{if $__wcf->session->getPermission('admin.user.canViewIpAddress') && $view->user->registrationIpAddress}
					<dt>{icon name='network-wired'} {lang}wcf.user.registrationIpAddress{/lang}</dt>
					<dd>{unsafe:$view->user->getRegistrationIpAddress()|ipSearch}</dd>
				{/if}

				{event name='afterMeta'}
			</dl>
		</div>
	</div>
</header>

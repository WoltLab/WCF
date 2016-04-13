<div class="box userProfileHeader">
	<div class="boxContent">
		<div class="userProfileCoverPhoto"></div>
		
		<div class="userProfileUser"{if $isAccessible}
			data-object-id="{@$user->userID}"
			{if $__wcf->session->getPermission('admin.user.canBanUser')}
				data-banned="{@$user->banned}"
			{/if}
			{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}
				data-disable-avatar="{@$user->disableAvatar}"
			{/if}
			{if $__wcf->session->getPermission('admin.user.canDisableSignature')}
				data-disable-signature="{@$user->disableSignature}"
			{/if}
			{/if}>
			
			<div class="layoutBoundary">
				<header class="contentHeader">
					{if $user->userID == $__wcf->user->userID}
						<a href="{link controller='AvatarEdit'}{/link}" class="contentHeaderIcon jsTooltip" title="{lang}wcf.user.avatar.edit{/lang}">{@$user->getAvatar()->getImageTag(128)}</a>
					{else}
						<span class="contentHeaderIcon">{@$user->getAvatar()->getImageTag(128)}</span>
					{/if}
					
					<div class="contentHeaderTitle">
						<h1 class="contentTitle">
							{$user->username}{if $user->banned} <span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}"></span>{/if}
						</h1>
						{if MODULE_USER_RANK}
							{if $user->getUserTitle()}
								<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
							{/if}
							{if $user->getRank() && $user->getRank()->rankImage}
								<span class="userRankImage">{@$user->getRank()->getImage()}</span>
							{/if}
						{/if}
					</div>
					
					<ul class="userProfileButtonContainer">
						{hascontent}
							<li>
								<a class="jsTooltip" title="{lang}wcf.user.profile.customization{/lang}"><span class="icon icon32 fa-pencil"></span> <span class="invisible">{lang}wcf.user.profile.customization{/lang}</span></a>
								<ul class="userProfileButtonMenu" data-menu="customization">
									{content}
										{event name='menuCustomization'}
										
										{if $user->userID == $__wcf->user->userID}
											<li><a href="{link controller='AvatarEdit'}{/link}">{lang}wcf.user.avatar.edit{/lang}</a></li>
										{/if}
										
										<li><a href="#" class="jsButtonEditCoverPhoto">todo: edit cover photo</a></li>
										
										{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
											<li class="divider"><a href="#" class="jsButtonEditProfile">{lang}wcf.user.editProfile{/lang}</a></li>
										{/if}
									{/content}
								</ul>
							</li>
						{/hascontent}
						
						<li>
							<a class="jsTooltip" title="{lang}wcf.user.profile.user{/lang}"><span class="icon icon32 fa-user"></span> <span class="invisible">{lang}wcf.user.profile.dropdown.interaction{/lang}</span></a>
							<ul class="userProfileButtonMenu" data-menu="interaction">
								{event name='menuInteraction'}
								
								{if $user->userID != $__wcf->user->userID}
									{if $user->isAccessible('canViewEmailAddress') || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
										<li><a href="mailto:{@$user->getEncodedEmail()}">{lang}wcf.user.button.mail{/lang}</a></li>
									{elseif $user->isAccessible('canMail') && $__wcf->session->getPermission('user.profile.canMail')}
										<li><a href="{link controller='Mail' object=$user}{/link}">{lang}wcf.user.button.mail{/lang}</a></li>
									{/if}
								{/if}
								
								{if $__wcf->session->getPermission('user.profile.canReportContent')}
									<li class="jsReportUser divider" data-object-id="{@$user->userID}"><a href="#">{lang}wcf.user.profile.report{/lang}</a></li>
								{/if}
							</ul>
						</li>
						
						{hascontent}
							<li>
								<a class="jsTooltip" title="{lang}wcf.user.searchUserContent{/lang}"><span class="icon icon32 fa-search"></span> <span class="invisible">{lang}wcf.user.searchUserContent{/lang}</span></a>
								<ul class="userProfileButtonMenu" data-menu="search">
									{content}{event name='menuSearch'}{/content}
								</ul>
							</li>
						{/hascontent}
						
						{hascontent}
							<li>
								<a class="jsTooltip" title="{lang}wcf.user.profile.management{/lang}"><span class="icon icon32 fa-cog"></span> <span class="invisible">{lang}wcf.user.profile.dropdown.management{/lang}</span></a>
								<ul class="userProfileButtonMenu" data-menu="management">
									{content}
										{if $isAccessible && $__wcf->user->userID != $user->userID && ($__wcf->session->getPermission('admin.user.canBanUser') || $__wcf->session->getPermission('admin.user.canDisableAvatar') || $__wcf->session->getPermission('admin.user.canDisableSignature') || ($__wcf->session->getPermission('admin.general.canUseAcp') && $__wcf->session->getPermission('admin.user.canEditUser')){event name='moderationDropdownPermissions'})}
											<li><a href="{link controller='UserEdit' object=$user isACP=true}{/link}" class="jsUserInlineEditor">{lang}wcf.user.moderate{/lang}</a></li>
										{/if}
									
										{event name='menuManagement'}
									{/content}
								</ul>
							</li>
						{/hascontent}
						
						{event name='buttonMenu'}
					</ul>
				</header>
			</div>
		</div>
		<div class="userProfileDetails">
			<div class="layoutBoundary">
				<ul class="inlineList commaSeparated">
					{if $user->isVisibleOption('gender') && $user->gender}<li>{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}</li>{/if}
					{if $user->isVisibleOption('birthday') && $user->getAge()}<li>{@$user->getAge()}</li>{/if}
					{if $user->isVisibleOption('location') && $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
					{if $user->getOldUsername()}<li>{lang}wcf.user.profile.oldUsername{/lang}</li>{/if}
					<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
					{event name='userDataRow1'}
				</ul>
				
				{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
					<ul class="inlineList commaSeparated">
						<li>{lang}wcf.user.usersOnline.lastActivity{/lang}: {@$user->getLastActivityTime()|time}</li>
						{if $user->getCurrentLocation()}<li>{@$user->getCurrentLocation()}</li>{/if}
					</ul>
				{/if}
				
				{hascontent}
					<dl class="plain inlineDataList">
						{content}
						{event name='statistics'}
						
						{if MODULE_LIKE && $user->likesReceived}
							<dt>{if !$user->isProtected()}<a href="{link controller='User' object=$user}{/link}#likes" class="jsTooltip" title="{lang}wcf.like.showLikesReceived{/lang}">{lang}wcf.like.likesReceived{/lang}</a>{else}{lang}wcf.like.likesReceived{/lang}{/if}</dt>
							<dd>{#$user->likesReceived}</dd>
						{/if}
						
						{if $user->activityPoints}
							<dt><a href="#" class="activityPointsDisplay jsTooltip" title="{lang}wcf.user.activityPoint.showActivityPoints{/lang}" data-user-id="{@$user->userID}">{lang}wcf.user.activityPoint{/lang}</a></dt>
							<dd>{#$user->activityPoints}</dd>
						{/if}
						
						{if $user->profileHits}
							<dt>{lang}wcf.user.profileHits{/lang}</dt>
							<dd{if $user->getProfileAge() > 1} title="{lang}wcf.user.profileHits.hitsPerDay{/lang}"{/if}>{#$user->profileHits}</dd>
						{/if}
						{/content}
					</dl>
				{/hascontent}
			</div>
		</div>
	</div>
	
</div>


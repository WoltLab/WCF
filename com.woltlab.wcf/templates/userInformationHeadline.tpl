<div class="containerHeadline">
	<h3>
		{user object=$user class='username'}
		{if $user->banned}
			<span class="jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}">
				{icon name='lock'}
			</span>
		{/if}
		{if MODULE_USER_RANK}
			{if $user->getUserTitle()}
				<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
			{/if}
			{if $user->getRank() && $user->getRank()->rankImage}
				<span class="userRankImage">{@$user->getRank()->getImage()}</span>
			{/if}
		{/if}
	</h3>
</div>
<ul class="inlineList commaSeparated">
	{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && !$user->isProtected()}
		{if $user->isVisibleOption('gender') && $user->gender}<li>{$user->getFormattedUserOption('gender')}</li>{/if}
		{if $user->isVisibleOption('birthday') && $user->getAge()}<li>{@$user->getAge()}</li>{/if}
		{if $user->isVisibleOption('location') && $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
	{/if}
	<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
	
	{event name='userData'}
</ul>

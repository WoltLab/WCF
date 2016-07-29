<div class="containerHeadline">
	<h3><a href="{link controller='User' object=$user}{/link}" class="username">{$user->username}</a></h3>
</div>
<div>
	{if $__wcf->getSession()->getPermission('user.profile.canViewUserProfile') && $user->isAccessible('canViewProfile')}
		{if $user->isVisibleOption('gender') && $user->gender}{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}, {/if}
		{if $user->isVisibleOption('birthday') && $user->getAge()}{@$user->getAge()}, {/if}
		{if $user->isVisibleOption('location') && $user->location}{lang}wcf.user.membersList.location{/lang}, {/if}
	{/if}
	{lang}wcf.user.membersList.registrationDate{/lang}
</div>

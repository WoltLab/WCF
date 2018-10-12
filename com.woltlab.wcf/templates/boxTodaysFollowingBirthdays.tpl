<ul class="sidebarItemList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box32">
			<a href="{link controller='User' object=$birthdayUserProfile}{/link}" aria-hidden="true">{@$birthdayUserProfile->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarItemTitle">
				<h3><a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="userLink" data-user-id="{@$birthdayUserProfile->userID}">{$birthdayUserProfile->username}</a></h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

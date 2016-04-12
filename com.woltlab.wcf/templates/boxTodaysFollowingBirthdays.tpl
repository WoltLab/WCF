<ul class="sidebarBoxList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box32">
			<a href="{link controller='User' object=$birthdayUserProfile}{/link}">{@$birthdayUserProfile->getAvatar()->getImageTag(32)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="userLink" data-user-id="{@$birthdayUserProfile->userID}">{$birthdayUserProfile->username}</a></h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

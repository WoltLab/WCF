<ul class="sidebarItemList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box32">
			{user object=$birthdayUserProfile type='avatar32' ariaHidden='true' tabindex='-1'}
			
			<div class="sidebarItemTitle">
				<h3>{user object=$birthdayUserProfile}</h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

<ol class="sidebarList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="sidebarListItem">
			<div class="sidebarListItem__image">
				{user object=$birthdayUserProfile type='avatar24' ariaHidden='true' tabindex='-1'}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{user object=$birthdayUserProfile class='sidebarListItem__link'}
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				<div class="sidebarListItem__meta__item sidebarListItem__meta__birthday">
					{$birthdayUserProfile->getBirthday()}
				</div>
			</div>
		</li>
	{/foreach}
</ol>

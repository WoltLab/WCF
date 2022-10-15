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

{if $birthdayUserProfiles|count >= 10}
	<button type="button" class="jsTodaysBirthdays button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</button>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/User/List'], ({ UserList }) => {
			let userList;
			document.querySelector('.jsTodaysBirthdays').addEventListener('click', () => {
				if (userList === undefined) {
					userList = new UserList({
						className: 'wcf\\data\\user\\UserBirthdayAction',
						parameters: {
							date: '{@TIME_NOW|date:'Y-m-d'}',
							sortField: '{$sortField}',
							sortOrder: '{$sortOrder}'
						}
					}, '{@$box->getTitle()|encodeJS} ({@TIME_NOW|date})');
				}

				userList.open();
			});
		});
	</script>
{/if}

<ul class="sidebarItemList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box32">
			{user object=$birthdayUserProfile type='avatar32' ariaHidden='true'}
			
			<div class="sidebarItemTitle">
				<h3>{user object=$birthdayUserProfile}</h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

{if $birthdayUserProfiles|count >= 10}
	<a class="jsTodaysBirthdays button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
	
	<script data-relocate="true">
		$(function() {
			var $todaysBirthdays = null;
			$('.jsTodaysBirthdays').click(function() {
				if ($todaysBirthdays === null) {
					$todaysBirthdays = new WCF.User.List('wcf\\data\\user\\UserBirthdayAction', '{@$box->getTitle()|encodeJS} ({@TIME_NOW|date})', {
						date: '{@TIME_NOW|date:'Y-m-d'}',
						sortField: '{$sortField}',
						sortOrder: '{$sortOrder}'
					});
				}
				$todaysBirthdays.open();
			});
		});
	</script>
{/if}

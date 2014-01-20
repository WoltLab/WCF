<ul class="sidebarBoxList">
	{foreach from=$birthdayUserProfiles item=birthdayUserProfile}
		<li class="box24">
			<a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="framed">{@$birthdayUserProfile->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarBoxHeadline">
				<h3><a href="{link controller='User' object=$birthdayUserProfile}{/link}" class="userLink" data-user-id="{@$birthdayUserProfile->userID}">{$birthdayUserProfile->username}</a></h3>
				<small>{$birthdayUserProfile->getBirthday()}</small>
			</div>
		</li>
	{/foreach}
</ul>

{if $birthdayUserProfiles|count >= 1}
	<a class="jsTodaysBirthdays button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</a>
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			var $todaysBirthdays = null;
			$('.jsTodaysBirthdays').click(function() {
				if ($todaysBirthdays === null) {
					$todaysBirthdays = new WCF.User.List('wcf\\data\\user\\UserBirthdayAction', '{lang}wcf.dashboard.box.com.woltlab.wcf.user.todaysBirthdays{/lang} ({@TIME_NOW|date})', { date: '{@TIME_NOW|date:'Y-m-d'}' });
				}
				$todaysBirthdays.open();
			});
		});
		//]]>
	</script>
{/if}
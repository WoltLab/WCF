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

{if $birthdayUserProfiles|count >= 10}
	<button type="button" class="jsTodaysBirthdays button small more jsOnly">{lang}wcf.global.button.showAll{/lang}</button>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Component/Dialog'], ({ dialogFactory }) => {
			document.querySelector('.jsTodaysBirthdays').addEventListener('click', () => {
				dialogFactory().usingListView().fromPreset(
					'{unsafe:$box->getTitle()|encodeJS} ({time type='plainDate' time=TIME_NOW})',
					'wcf\\system\\listView\\user\\UserBirthdayListView',
					new Map([['date', '{time type='custom' time=TIME_NOW format='Y-m-d'}']])
				);
			});
		});
	</script>
{/if}

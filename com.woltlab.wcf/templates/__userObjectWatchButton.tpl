{if $__wcf->user->userID}
	<div class="dropdown contentInteractionButton">
		<button type="button" class="jsTooltip button small dropdownToggle jsSubscribeButton userObjectWatchDropdownToggle{if $isSubscribed} active{/if}" data-object-type="{$objectType}" data-object-id="{$objectID}" data-is-subscribed="{if $isSubscribed}1{else}0{/if}">
			{if $isSubscribed}
				{icon name='bookmark' type='solid'}
			{else}
				{icon name='bookmark'}
			{/if}
			<span>{if $isSubscribed}{lang}wcf.user.objectWatch.button.subscribed{/lang}{else}{lang}wcf.user.objectWatch.button.subscribe{/lang}{/if}</span>
		</button>
		<ul class="dropdownMenu userObjectWatchDropdown" data-object-type="{$objectType}" data-object-id="{$objectID}">
			<li class="userObjectWatchSelect{if !$isSubscribed} active{/if}" data-subscribe="0">
				<span class="userObjectWatchSelectHeader">{lang}wcf.user.objectWatch.notSubscribed{/lang}</span>
				<span class="userObjectWatchSelectDescription">{lang}wcf.user.objectWatch.notSubscribed.description{/lang}</span>
			</li>
			<li class="userObjectWatchSelect{if $isSubscribed} active{/if}" data-subscribe="1">
				<span class="userObjectWatchSelectHeader">{lang}wcf.user.objectWatch.subscribed{/lang}</span>
				<span class="userObjectWatchSelectDescription">{lang}wcf.user.objectWatch.subscribed.description{/lang}</span>
			</li>
		</ul>
	</div>

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/User/ObjectWatch', 'WoltLabSuite/Core/Language'], (ObjectWatch, Language) => {
			Language.addObject({
				'wcf.user.objectWatch.button.subscribe': '{jslang}wcf.user.objectWatch.button.subscribe{/jslang}',
				'wcf.user.objectWatch.button.subscribed': '{jslang}wcf.user.objectWatch.button.subscribed{/jslang}',
			})

			ObjectWatch.setup();
		});
	</script>
{/if}

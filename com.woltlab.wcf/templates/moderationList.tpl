{capture assign='pageTitle'}{lang}wcf.moderation.items{/lang}{if $gridView->getPageNo() > 1} - {lang pageNo=$gridView->getPageNo()}wcf.page.pageNo{/lang}{/if}{/capture}
{capture assign='contentTitle'}{lang}wcf.moderation.items{/lang}{/capture}

{capture assign='sidebarRight'}
	{event name='sidebarBoxes'}
{/capture}

{capture assign='contentInteractionButtons'}
	{if $gridView->canMarkAsRead()}
		<button type="button" class="markAllModerationQueuesAsReadButton contentInteractionButton button small jsOnly">
			{icon name='check'}
			<span>{lang}wcf.global.button.markAllAsRead{/lang}</span>
		</button>

		<script data-relocate="true">
			require(['WoltLabSuite/Core/Component/Moderation/MarkAllModerationQueuesAsRead'], ({ setup }) => {
				setup(
					document.querySelector('.markAllModerationQueuesAsReadButton'),
					document.getElementById('{unsafe:$gridView->getID()|encodeJS}_table')
				);
			});
		</script>
	{/if}
	<a href="{link controller='DeletedContentList'}{/link}" class="contentInteractionButton button small">{icon name='trash-can'} <span>{lang}wcf.moderation.showDeletedContent{/lang}</span></a>
{/capture}

{include file='header'}

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}

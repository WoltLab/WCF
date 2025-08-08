<div class="contentHeaderTitle">
	<h1 class="contentTitle">{$title}: {$queue->getTitle()}</h1>
	<ul class="inlineList contentHeaderMetaData">
		{event name='beforeMetaData'}

		{if $queue->lastChangeTime}
			<li title="{lang}wcf.moderation.lastChangeTime{/lang}">
				{icon name='clock'}
				{time time=$queue->lastChangeTime}
			</li>
		{/if}

		<li title="{lang}wcf.moderation.assignedUser{/lang}">
			{icon name='user'}
			{if $queue->assignedUserID}
				<a href="{link controller='User' id=$queue->assignedUserID}{/link}" class="userLink" data-object-id="{$queue->assignedUserID}">{$queue->assignedUsername}</a>
			{else}
				{lang}wcf.moderation.assignedUser.nobody{/lang}
			{/if}
		</li>

		<li title="{lang}wcf.moderation.status{/lang}">
			{icon name='arrows-rotate'}
			{$queue->getStatus()}
		</li>

		{event name='afterMetaData'}
	</ul>
</div>

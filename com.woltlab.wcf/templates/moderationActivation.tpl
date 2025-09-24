{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}</h1>
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
					<span id="moderationAssignedUser">
						{if $queue->assignedUserID}
							<a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-object-id="{$assignedUserID}">{$queue->assignedUsername}</a>
						{else}
							{lang}wcf.moderation.assignedUser.nobody{/lang}
						{/if}
					</span>
				</li>

				<li title="{lang}wcf.moderation.status{/lang}">
					{icon name='arrows-rotate'}
					<span id="moderationQueueStatus">{$queue->getStatus()}</span>
				</li>

				{event name='afterMetaData'}
			</ul>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $queue->getAffectedObject()}<li><a href="{$queue->getAffectedObject()->getLink()}" class="button buttonPrimary">{icon name='arrow-right'} <span>{lang}wcf.moderation.jumpToContent{/lang}</span></a></li>{/if}
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{capture assign='contentInteractionButtons'}
	<button
		type="button"
		id="moderationAssignUser"
		class="contentInteractionButton button small jsOnly"
		data-url="{$queue->endpointAssignUser()}"
	>
		{icon name='user-plus' type='solid'}
		<span>{lang}wcf.moderation.assignedUser.change{/lang}</span>
	</button>
	{if !$queue->isDone()}
		<button
			type="button"
			id="enableContent"
			class="contentInteractionButton button small jsOnly"
			data-object-id="{$queue->queueID}"
			data-redirect-url="{link controller='ModerationList'}filters[status]=0{/link}"
		>
			{icon name='check'}
			<span>{lang}wcf.moderation.activation.enableContent{/lang}</span>
		</button>
		{if $queueManager->canRemoveContent($queue->getDecoratedObject())}
			<button
				type="button"
				id="removeContent"
				class="contentInteractionButton button small jsOnly"
				data-object-id="{$queue->queueID}"
				data-object-name="{$queue->getTitle()}"
				data-redirect-url="{link controller='ModerationList'}filters[status]=0{/link}"
			>
				{icon name='xmark'}
				<span>{lang}wcf.moderation.activation.removeContent{/lang}</span>
			</button>
		{/if}
	{/if}
{/capture}

{include file='header'}

{include file='shared_formError'}

<section class="section sectionContainerList">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.activation.content{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{$queue->getObjectTypeName()}{/lang}</p>
	</header>

	{unsafe:$disabledContent}
</section>

<section id="comments" class="section sectionContainerList moderationComments">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $queue->comments} <span class="badge">{#$queue->comments}</span>{/if}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.comments.description{/lang}</p>
	</header>
	
	{include file='comments' commentContainerID='moderationQueueCommentList' commentObjectID=$queueID}
</section>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/Moderation/AssignUser'], ({ setup }) => {
		{jsphrase name='wcf.moderation.assignedUser.nobody'}
		
		setup(document.getElementById('moderationAssignUser'));
	});

	{if !$queue->isDone()}
		require(['WoltLabSuite/Core/Controller/Moderation/Activation'], ({ setup }) => {
			{jsphrase name='wcf.moderation.activation.enableContent.confirmMessage'}
			
			setup(
				document.getElementById('enableContent'),
				document.getElementById('removeContent'),
			);
		});
	{/if}
</script>

{include file='footer'}

{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
			
			{if $queue->lastChangeTime}
				<dl class="plain inlineDataList">
					<dt>{lang}wcf.moderation.lastChangeTime{/lang}</dt>
					<dd>{@$queue->lastChangeTime|time}</dd>
				</dl>
			{/if}
			
			<dl class="plain inlineDataList" id="moderationAssignedUserContainer">
				<dt>{lang}wcf.moderation.assignedUser{/lang}</dt>
				<dd id="moderationAssignedUser">
					{if $queue->assignedUserID}
						<a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-object-id="{@$assignedUserID}">{$queue->assignedUsername}</a>
					{else}
						{lang}wcf.moderation.assignedUser.nobody{/lang}
					{/if}
				</dd>
			</dl>
			
			<dl class="plain inlineDataList" id="moderationStatusContainer">
				<dt>{lang}wcf.moderation.status{/lang}</dt>
				<dd id="moderationQueueStatus">{$queue->getStatus()}</dd>
			</dl>
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
		<button type="button" id="enableContent" class="contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.moderation.activation.enableContent{/lang}</span></button>
		{if $queueManager->canRemoveContent($queue->getDecoratedObject())}<button type="button" id="removeContent" class="contentInteractionButton button small jsOnly">{icon name='xmark'} <span>{lang}wcf.moderation.activation.removeContent{/lang}</span></button>{/if}
	{/if}
{/capture}

{include file='header'}

{include file='shared_formError'}

<section class="section sectionContainerList">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.activation.content{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{@$queue->getObjectTypeName()}{/lang}</p>
	</header>

	{@$disabledContent}
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
	
	$(function() {
		WCF.Language.addObject({
			'wcf.moderation.activation.enableContent.confirmMessage': '{jslang}wcf.moderation.activation.enableContent.confirmMessage{/jslang}',
			'wcf.moderation.activation.removeContent.confirmMessage': '{jslang}wcf.moderation.activation.removeContent.confirmMessage{/jslang}',
		});
		
		new WCF.Moderation.Activation.Management({@$queue->queueID}, '{link controller='ModerationList' encode=false}{/link}');
	});
</script>

{include file='footer'}

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
				<dd>
					<span>
						{if $queue->assignedUserID}
							<a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-object-id="{@$assignedUserID}">{$queue->assignedUsername}</a>
						{else}
							{lang}wcf.moderation.assignedUser.nobody{/lang}
						{/if}
					</span>
				</dd>
			</dl>
			
			<dl class="plain inlineDataList" id="moderationStatusContainer">
				<dt>{lang}wcf.moderation.status{/lang}</dt>
				<dd>{$queue->getStatus()}</dd>
			</dl>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						<li class="jsOnly"><a id="moderationAssignUser" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.moderation.assignedUser.change{/lang}</span></a></li>
						{if !$queue->isDone()}
							<li class="jsOnly"><a id="enableContent" class="button"><span class="icon icon16 fa-check"></span> <span>{lang}wcf.moderation.activation.enableContent{/lang}</span></a></li>
							{if $queueManager->canRemoveContent($queue->getDecoratedObject())}<li class="jsOnly"><a id="removeContent" class="button"><span class="icon icon16 fa-times"></span> <span>{lang}wcf.moderation.activation.removeContent{/lang}</span></a></li>{/if}
						{/if}
						{if $queue->getAffectedObject()}<li><a href="{$queue->getAffectedObject()->getLink()}" class="button"><span class="icon icon16 fa-arrow-right"></span> <span>{lang}wcf.moderation.jumpToContent{/lang}</span></a></li>{/if}
						
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{include file='header'}

{include file='formError'}

<section class="section sectionContainerList">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.activation.content{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{@$queue->getObjectTypeName()}{/lang}</p>
	</header>

	{@$disabledContent}
</section>

{include file='__commentJavaScript' commentContainerID='moderationQueueCommentList'}

<section id="comments" class="section sectionContainerList moderationComments">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $queue->comments} <span class="badge">{#$queue->comments}</span>{/if}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.comments.description{/lang}</p>
	</header>
	
	<ul id="moderationQueueCommentList" class="commentList containerList" data-can-add="true" data-object-id="{@$queueID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
		{include file='commentListAddComment' wysiwygSelector='moderationQueueCommentListAddComment'}
		{include file='commentList'}
	</ul>
</section>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'wcf.moderation.activation.enableContent.confirmMessage': '{jslang}wcf.moderation.activation.enableContent.confirmMessage{/jslang}',
			'wcf.moderation.activation.removeContent.confirmMessage': '{jslang}wcf.moderation.activation.removeContent.confirmMessage{/jslang}',
			'wcf.moderation.assignedUser': '{jslang}wcf.moderation.assignedUser{/jslang}',
			'wcf.moderation.assignedUser.error.notAffected': '{jslang}wcf.moderation.assignedUser.error.notAffected{/jslang}',
			'wcf.moderation.status.outstanding': '{jslang}wcf.moderation.status.outstanding{/jslang}',
			'wcf.moderation.status.processing': '{jslang}wcf.moderation.status.processing{/jslang}',
			'wcf.user.username.error.notFound': '{jslang __literal=true}wcf.user.username.error.notFound{/jslang}'
		});
		
		new WCF.Moderation.Activation.Management({@$queue->queueID}, '{link controller='ModerationList' encode=false}{/link}');
	});
</script>

{include file='footer'}

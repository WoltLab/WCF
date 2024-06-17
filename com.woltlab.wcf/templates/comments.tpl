<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Component/Comment/List'], (Language, { setup }) => {
		Language.addObject({
			'wcf.comment.more': '{jslang}wcf.comment.more{/jslang}',
			'wcf.comment.response.more': '{jslang}wcf.comment.response.more{/jslang}',
		});

		setup('{@$commentContainerID|encodeJS}');
	});
</script>

{event name='javascriptInclude'}

<div class="commentListContainer"
	id="{$commentContainerID}"
	data-can-add="{if $commentCanAdd}true{else}false{/if}"
	data-object-id="{$commentObjectID}"
	data-object-type-id="{$commentObjectTypeID}"
	data-comments="{$commentList->countObjects()}"
	data-last-comment-time="{$lastCommentTime}"
	data-enable-reactions="{if MODULE_LIKE && $commentList->getCommentManager()->supportsLike() && ($__wcf->getSession()->getPermission('user.like.canViewLike') || $__wcf->getSession()->getPermission('user.like.canLike'))}true{else}false{/if}"
>
	<div class="commentList">
		{if $commentCanAdd}
			{capture assign=_commentAddWysiwygSelector}{$commentContainerID}AddComment{/capture}
			<div class="commentList__item">
				<div class="commentAdd commentAdd--collapsed">
					<div class="commentAdd__avatar">
						{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}
					</div>
					
					<div class="commentAdd__content commentAdd__content--collapsed jsOuterEditorContainer">
						<button type="button" class="commentAdd__placeholder">
							{icon size=32 name='reply'}
							{lang}wcf.comment.add{/lang}
						</button>
						<div class="commentAdd__editor" hidden>
							{if !$commentList->getCommentManager()->canAddWithoutApproval($commentList->objectID)}
								<p class="info jsCommentAddRequiresApproval">{lang}wcf.comment.moderation.info{/lang}</p>
							{/if}
							
							<textarea id="{$_commentAddWysiwygSelector}" name="text" class="wysiwygTextarea"
									data-disable-attachments="true"
									data-support-mention="true"
							></textarea>
							{include file='messageFormTabsInline' wysiwygContainerID=$_commentAddWysiwygSelector wysiwygSelector=$_commentAddWysiwygSelector}
							
							{* in-template call for full backwards-compatibility *}
							{$commentList->getCommentManager()->setDisallowedBBCodes()}

							{include file='shared_wysiwyg' wysiwygSelector=$_commentAddWysiwygSelector}
							
							<div class="formSubmit">
								<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
								
								{include file='messageFormPreviewButton' previewMessageFieldID=$_commentAddWysiwygSelector previewButtonID=$_commentAddWysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
		
		{include file='commentList'}
	</div>

	{if $commentCanAdd}
		{* comment response, editor instance will be re-used *}
		{capture assign=_commentResponseWysiwygSelector}{$commentContainerID}AddCommentResponse{/capture}
		<div class="commentResponseAdd" hidden>
			<div class="commentResponseAdd__avatar">
				{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}
			</div>

			<div class="commentResponseAdd__content jsOuterEditorContainer">
				<div class="commentResponseAdd__editor">
					{if !$commentList->getCommentManager()->canAddWithoutApproval($commentList->objectID)}
						<p class="info jsCommentAddRequiresApproval">{lang}wcf.comment.moderation.info{/lang}</p>
					{/if}
					
					<textarea id="{$_commentResponseWysiwygSelector}" name="text" class="wysiwygTextarea"
							  data-disable-attachments="true"
							  data-support-mention="true"
					></textarea>
					{include file='messageFormTabsInline' wysiwygContainerID=$_commentResponseWysiwygSelector wysiwygSelector=$_commentResponseWysiwygSelector}
					
					{* in-template call for full backwards-compatibility *}
					{$commentList->getCommentManager()->setDisallowedBBCodes()}

					{include file='shared_wysiwyg' wysiwygSelector=$_commentResponseWysiwygSelector}
					
					<div class="formSubmit">
						<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
						
						{include file='messageFormPreviewButton' previewMessageFieldID=$_commentResponseWysiwygSelector previewButtonID=$_commentResponseWysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment.response' previewMessageObjectID=0}
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>

<script data-relocate="true">
	{*$(function() {
		WCF.Language.addObject({
			'wcf.comment.add': '{jslang}wcf.comment.add{/jslang}',
			'wcf.comment.button.response.add': '{jslang}wcf.comment.button.response.add{/jslang}',
			'wcf.comment.delete.confirmMessage': '{jslang}wcf.comment.delete.confirmMessage{/jslang}',
			'wcf.comment.description': '{jslang}wcf.comment.description{/jslang}',
			'wcf.comment.guestDialog.title': '{jslang}wcf.comment.guestDialog.title{/jslang}',
			'wcf.comment.more': '{jslang}wcf.comment.more{/jslang}',
			'wcf.comment.response.add': '{jslang}wcf.comment.response.add{/jslang}',
			'wcf.comment.response.more': '{jslang}wcf.comment.response.more{/jslang}',
			'wcf.message.error.editorAlreadyInUse': '{jslang}wcf.message.error.editorAlreadyInUse{/jslang}',
			'wcf.moderation.report.reportContent': '{jslang}wcf.moderation.report.reportContent{/jslang}',
			'wcf.moderation.report.success': '{jslang}wcf.moderation.report.success{/jslang}'
		});
		
		new {if $commentHandlerClass|isset}{@$commentHandlerClass}{else}WCF.Comment.Handler{/if}('{@$commentContainerID}');
			require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
				
			});
	});*}

	require(['WoltLabSuite/Core/Component/Comment/Handler', ], ({ setup }) => {
		setup('{@$commentContainerID}');
	});

	{if MODULE_LIKE && $commentList->getCommentManager()->supportsLike() && $__wcf->getSession()->getPermission('user.like.canViewLike') || $__wcf->getSession()->getPermission('user.like.canLike')}
		require(['WoltLabSuite/Core/Ui/Reaction/Handler'], (UiReactionHandler) => {
			new UiReactionHandler('com.woltlab.wcf.comment', {
				// selectors
				containerSelector: '#{@$commentContainerID} li.comment',
				summaryListSelector: '.reactionSummaryList',
				isButtonGroupNavigation: true
			});
			
			new UiReactionHandler('com.woltlab.wcf.comment.response', {
				// selectors
				containerSelector: '#{@$commentContainerID} .commentResponse',
				summaryListSelector: '.reactionSummaryList',
				isButtonGroupNavigation: true,
				buttonSelector: '.reactButtonCommentResponse'
			});
		});
	{/if}
</script>

{event name='javascriptInclude'}

<div class="commentListContainer"
    id="{@$commentContainerID}"
    data-can-add="{if $commentCanAdd}true{else}false{/if}"
    data-object-id="{@$commentObjectID}"
    data-object-type-id="{@$commentObjectTypeID}"
    data-comments="{@$commentList->countObjects()}"
    data-last-comment-time="{@$lastCommentTime}"
>
    <ul class="commentList containerList">
    	{if $commentCanAdd}
            {capture assign=_commentAddWysiwygSelector}{$commentContainerID}AddComment{/capture}
            <li class="commentAdd">
            	<div class="commentAdd__avatar">
            		{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(48)}
            	</div>
            	
            	<div class="commentAdd__content commentAdd__content--collapsed jsOuterEditorContainer">
            		<button type="button" class="commentAdd__placeholder">
            			{icon size=32 name='reply'}
            			{lang}wcf.comment.add{/lang}
            		</button>
            		<div class="commentAdd__editor">
            			{if !$commentList->getCommentManager()->canAddWithoutApproval($commentList->objectID)}
            				<p class="info jsCommentAddRequiresApproval">{lang}wcf.comment.moderation.info{/lang}</p>
            			{/if}
            			
            			<textarea id="{$_commentAddWysiwygSelector}" name="text" class="wysiwygTextarea"
            			          data-disable-attachments="true"
            			          data-support-mention="true"
            			></textarea>
            			{include file='messageFormTabsInline'}
            			
            			{* in-template call for full backwards-compatibility *}
            			{$commentList->getCommentManager()->setDisallowedBBCodes()}
            			
            			{include file='wysiwyg' wysiwygSelector=$_commentAddWysiwygSelector}
            			
            			<div class="formSubmit">
            				<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
            				
            				{include file='messageFormPreviewButton' previewMessageFieldID=$_commentAddWysiwygSelector previewButtonID=$_commentAddWysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
            			</div>
            		</div>
            	</div>
            </li>
        {/if}
    	{include file='commentList'}
    </ul>

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
            		{include file='messageFormTabsInline' wysiwygSelector=$_commentResponseWysiwygSelector}
            		
            		{* in-template call for full backwards-compatibility *}
            		{$commentList->getCommentManager()->setDisallowedBBCodes()}
            		
            		{include file='wysiwyg' wysiwygSelector=$_commentResponseWysiwygSelector}
            		
            		<div class="formSubmit">
            			<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
            			
            			{include file='messageFormPreviewButton' previewMessageFieldID=$_commentResponseWysiwygSelector previewButtonID=$_commentResponseWysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment.response' previewMessageObjectID=0}
            		</div>
            	</div>
            </div>
        </div>
    {/if}
</div>

<li class="box48 jsCommentAdd jsOnly">
	{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(48)}
	<div class="commentListAddComment collapsed jsOuterEditorContainer" data-placeholder="{lang}wcf.comment.add{/lang}">
		<div class="commentListAddCommentEditorContainer">
			{if !$commentList->getCommentManager()->canAddWithoutApproval($commentList->objectID)}
				<p class="info jsCommentAddRequiresApproval">{lang}wcf.comment.moderation.info{/lang}</p>
			{/if}
			
			<textarea id="{$wysiwygSelector}" name="text" class="wysiwygTextarea"
			          data-disable-attachments="true"
			          data-disable-media="true"
			></textarea>
			{include file='messageFormTabsInline'}
			
			{* in-template call for full backwards-compatibility *}
			{$commentList->getCommentManager()->setDisallowedBBCodes()}
			
			{include file='wysiwyg'}
			
			<div class="formSubmit">
				<button class="buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
				
				{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
			</div>
		</div>
	</div>
</li>

{* comment response, editor instance will be re-used *}
{capture assign=_commentResponseWysiwygSelector}{$wysiwygSelector}Response{/capture}
<li class="jsCommentResponseAddContainer" style="display: none">
	<div class="box32 jsCommentResponseAdd jsOnly">
		{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}
		<div class="commentListAddCommentResponse collapsed jsOuterEditorContainer" data-placeholder="{lang}wcf.comment.response.add{/lang}">
			<div class="commentListAddCommentResponseEditorContainer">
				{if !$commentList->getCommentManager()->canAddWithoutApproval($commentList->objectID)}
					<p class="info jsCommentAddRequiresApproval">{lang}wcf.comment.moderation.info{/lang}</p>
				{/if}
				
				<textarea id="{$_commentResponseWysiwygSelector}" name="text" class="wysiwygTextarea"
				          data-disable-attachments="true"
				          data-disable-media="true"
				></textarea>
				{include file='messageFormTabsInline' wysiwygSelector=$_commentResponseWysiwygSelector}
				
				{* in-template call for full backwards-compatibility *}
				{$commentList->getCommentManager()->setDisallowedBBCodes()}
				
				{include file='wysiwyg' wysiwygSelector=$_commentResponseWysiwygSelector}
				
				<div class="formSubmit">
					<button class="buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
					
					{include file='messageFormPreviewButton' previewMessageFieldID=$_commentResponseWysiwygSelector previewButtonID=$_commentResponseWysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment.response' previewMessageObjectID=0}
				</div>
			</div>
		</div>
	</div>
</li>

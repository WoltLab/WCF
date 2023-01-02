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
			
			<textarea id="{$wysiwygSelector}" name="text" class="wysiwygTextarea"
			          data-disable-attachments="true"
			          data-support-mention="true"
			></textarea>
			{include file='messageFormTabsInline'}
			
			{* in-template call for full backwards-compatibility *}
			{$commentList->getCommentManager()->setDisallowedBBCodes()}
			
			{include file='wysiwyg'}
			
			<div class="formSubmit">
				<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
				
				{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
			</div>
		</div>
	</div>
</li>

{* comment response, editor instance will be re-used *}
{capture assign=_commentResponseWysiwygSelector}{$wysiwygSelector}Response{/capture}
<li class="commentAddResponse" hidden>
	<div class="commentAddResponse__avatar">
		{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}
	</div>
	
	<div class="commentAddResponse__editor">
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
</li>

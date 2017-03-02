<li class="box48 jsCommentAdd jsOnly">
	{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(48)}
	<div class="commentListAddComment collapsed" data-placeholder="{lang}wcf.comment.add{/lang}">
		<div class="commentListAddCommentEditorContainer">
			<textarea id="{$wysiwygSelector}" name="text" class="wysiwygTextarea"
			          data-disable-attachments="true"
			          data-disable-media="true"
			></textarea>
			{include file='wysiwyg'}
			
			<div class="formSubmit">
				<button class="buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
				
				{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
			</div>
		</div>
	</div>
</li>


<li class="box48 jsCommentAdd jsOnly">
	{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(48)}
	<div class="commentListAddComment collapsed" data-placeholder="{lang}wcf.comment.add{/lang}">
		<div class="commentListAddCommentEditorContainer">
			<textarea id="text" name="text" class="wysiwygTextarea"
			          data-disable-attachments="true"
			          data-disable-media="true"
			          data-support-mention="true"
			></textarea>
			{include file='wysiwyg' userProfileCommentList=$commentListContainerID}
			
			<div class="formSubmit">
				<button class="buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
				
				{include file='messageFormPreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=0}
			</div>
		</div>
	</div>
</li>


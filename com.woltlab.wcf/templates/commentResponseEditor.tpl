{capture assign='wysiwygSelector'}commentResponseEditor{@$response->responseID}{/capture}
<textarea id="{$wysiwygSelector}" class="wysiwygTextarea"
          data-disable-attachments="true"
          data-support-mention="true"
>{$text}</textarea>
{include file='messageFormTabsInline' wysiwygContainerID=$wysiwygSelector wysiwygSelector=$wysiwygSelector}

<div class="formSubmit">
	<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	
	{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment.response' previewMessageObjectID=$response->responseID}
	
	<button type="button" class="button" data-type="cancel">{lang}wcf.global.button.cancel{/lang}</button>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Component/Comment/Add'], ({ setCommentEditorFeatures }) => {
		setCommentEditorFeatures(document.getElementById('{$wysiwygSelector}'));
	});
</script>

{include file='shared_wysiwyg' wysiwygSelector=$wysiwygSelector}

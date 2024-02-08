{capture assign='wysiwygSelector'}commentEditor{@$comment->commentID}{/capture}
<textarea id="{$wysiwygSelector}" class="wysiwygTextarea"
          data-disable-attachments="true"
          data-support-mention="true"
>{$text}</textarea>
{include file='messageFormTabsInline'}

<div class="formSubmit">
	<button type="button" class="button buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	
	{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=$comment->commentID}
	
	<button type="button" class="button" data-type="cancel">{lang}wcf.global.button.cancel{/lang}</button>
</div>

{include file='shared_wysiwyg'}

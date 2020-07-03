{capture assign='wysiwygSelector'}commentEditor{@$comment->commentID}{/capture}
<textarea id="{$wysiwygSelector}" class="wysiwygTextarea"
          data-disable-attachments="true"
          data-disable-media="true"
>{$comment->message}</textarea>
{include file='messageFormTabsInline'}

<div class="formSubmit">
	<button class="buttonPrimary" data-type="save" accesskey="s">{lang}wcf.global.button.submit{/lang}</button>
	
	{include file='messageFormPreviewButton' previewMessageFieldID=$wysiwygSelector previewButtonID=$wysiwygSelector|concat:'_PreviewButton' previewMessageObjectType='com.woltlab.wcf.comment' previewMessageObjectID=$comment->commentID}
	
	<button data-type="cancel">{lang}wcf.global.button.cancel{/lang}</button>
</div>

{include file='wysiwyg'}

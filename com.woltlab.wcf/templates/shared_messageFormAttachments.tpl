<div class="messageTabMenuContent" id="attachments_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}">
	<button type="button">
		<woltlab-core-file-upload
			data-endpoint="{link controller='FileUploadPreflight'}{/link}"
			data-type-name="com.woltlab.wcf.attachment"
			data-context-object-type=""
			data-context-object-id=""
		></woltlab-core-file-upload>
	</button>
	<dl class="wide">
		<dt></dt>
		<dd>
			<div data-max-size="{@$attachmentHandler->getMaxSize()}"></div>
			<small>{lang}wcf.attachment.upload.limits{/lang}</small>
		</dd>
	</dl>
	
	{event name='fields'}
</div>

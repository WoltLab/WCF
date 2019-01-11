<dl{if $errorField == $fieldId} class="formError"{/if}>
	<dt><label for="{$fieldId}">{$field->getName()}</label></dt>
	<dd>
		{if !$field->supportMultipleFiles() && $field->isImageOnly()}
			<div class="selectedImagePreview uploadedFile" id="{$fieldId}uploadFileList" data-internal-id="{$field->getInternalId()}">{*
				*}{if !$files|empty}{*
					*}{assign var="file" value=$files|reset}{*
					*}<img src="{$file->getImage()}" alt="" class="previewImage" id="{$fieldId}Image" style="max-width: 100%" data-unique-file-id="{$file->getUniqueFileId()}">{*
				*}
					<ul class="buttonGroup"></ul>
				{/if}{*
			*}</div>
		{else}
			<div class="formUploadHandlerContent">
				<ul class="formUploadHandlerList" id="{$fieldId}uploadFileList" data-internal-id="{$field->getInternalId()}">
					{foreach from=$files item=file}
						<li class="box64 uploadedFile" data-unique-file-id="{$file->getUniqueFileId()}">
							<span class="icon icon64 fa-{$file->getIconName()}"></span>
							
							<div>
								<div>
									<p>{$file->getFilename()}</p>
									<small>{@$file->filesize|filesize}</small>
								</div>
								
								<ul class="buttonGroup"></ul>
								
								{if $errorField == $file->getUniqueFileId()}
									<small class="innerError innerFileError">{lang __optional="true"}{$errorType}{/lang}</small>
								{/if}
							</div>
						</li>
					{/foreach}
				</ul>
			</div>
		{/if}
		
		<div id="{$fieldId}UploadButtonDiv" class="uploadButtonDiv"></div>
		
		{if $errorField == $fieldId}
			<small class="innerError">
				{if $errorType == 'empty'}
					{lang}wcf.global.form.error.empty{/lang}
				{else}
					{lang}{$errorType}{/lang}
				{/if}
			</small>
		{/if}
		
		<input type="hidden" name="{$fieldId}" value="{$field->getInternalId()}">
	</dd>
</dl>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/File/Upload', 'Language'], function(Upload, Language) {
		new Upload("{$fieldId}UploadButtonDiv", "{$fieldId}uploadFileList", {
			internalId: '{$field->getInternalId()}',
			maxFiles: {$field->getMaxFiles()},
			imagePreview: {if !$field->supportMultipleFiles() && $field->isImageOnly()}true{else}false{/if}
		});
		
		Language.addObject({
			'wcf.upload.error.reachedRemainingLimit': '{lang}wcf.upload.error.reachedRemainingLimit{/lang}',
			'wcf.upload.error.noImage': '{lang}wcf.upload.error.noImage{/lang}'
		});
	});
</script>
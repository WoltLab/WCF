{if !$uploadField->supportMultipleFiles() && $uploadField->isImageOnly()}
	<div class="selectedImagePreview uploadedFile" id="{$uploadFieldId}uploadFileList" data-internal-id="{$uploadField->getInternalId()}">{*
		*}{if !$files|empty}{*
			*}{assign var="file" value=$uploadFieldFiles|reset}{*
			*}<img src="{$file->getImage()}" alt="" class="previewImage" id="{$uploadFieldId}Image" style="max-width: 100%" data-unique-file-id="{$file->getUniqueFileId()}">{*
		*}
			<ul class="buttonGroup"></ul>
		{/if}{*
	*}</div>
{else}
	<div class="formUploadHandlerContent">
		<ul class="formUploadHandlerList" id="{$uploadFieldId}uploadFileList" data-internal-id="{$uploadField->getInternalId()}">
			{foreach from=$uploadFieldFiles item=file}
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

<div id="{$uploadFieldId}UploadButtonDiv" class="uploadButtonDiv"></div>

<input type="hidden" name="{$uploadFieldId}" value="{$uploadField->getInternalId()}">

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/File/Upload', 'Language'], function(Upload, Language) {
		new Upload("{$uploadFieldId}UploadButtonDiv", "{$uploadFieldId}uploadFileList", {
			internalId: '{$uploadField->getInternalId()}',
			maxFiles: {$uploadField->getMaxFiles()},
			imagePreview: {if !$uploadField->supportMultipleFiles() && $uploadField->isImageOnly()}true{else}false{/if}
		});
		
		Language.addObject({
			'wcf.upload.error.reachedRemainingLimit': '{lang}wcf.upload.error.reachedRemainingLimit{/lang}',
			'wcf.upload.error.noImage': '{lang}wcf.upload.error.noImage{/lang}'
		});
	});
</script>
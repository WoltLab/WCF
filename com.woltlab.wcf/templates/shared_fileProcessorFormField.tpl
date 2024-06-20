{unsafe:$fileProcessorHtmlElement}

{assign var="files" value=$field->getFiles()}
{if $field->isSingleFileUpload() && $imageOnly}
	<div class="fileUpload__preview">
		{if $field->getValue()}
			{assign var="file" value=$files|reset}
			{unsafe:$file->toHtmlElement()}
		{/if}
	</div>
{else}
	<ul class="fileList">
		{foreach from=$files item=file}
			<li class="fileList__item">
				{unsafe:$file->toHtmlElement()}
			</li>
		{/foreach}
	</ul>
{/if}

<script data-relocate="true">
	{jsphrase name='wcf.global.button.replace'}

	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/FileProcessor"], ({ FileProcessor }) => {
		new FileProcessor(
			'{@$field->getPrefixedId()|encodeJS}',
			{if $field->isSingleFileUpload()}true{else}false{/if},
			{if $imageOnly}true{else}false{/if}
		);
	});
</script>

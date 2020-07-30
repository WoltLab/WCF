{if $field->isImmutable() && $field->getValue()}
	{if $field->getMedia()->isImage && $field->getMedia()->hasThumbnail('small')}
		<div id="{@$field->getPrefixedId()}_preview" class="selectedImagePreview">
			{@$field->getMedia()->getThumbnailTag('small')}
		</div>
	{else}
		<div class="box16 selectedImagePreview">
			{@$field->getMedia()->getElementTag(16)}
			
			<p>{$field->getMedia()->getTitle()}</p>
		</div>
	{/if}
{else}
	{if $field->isImageOnly()}
		<div id="{@$field->getPrefixedId()}_preview" class="selectedImagePreview">
			{if $field->getValue() && $field->getMedia()->hasThumbnail('small')}
				{@$field->getMedia()->getThumbnailTag('small')}
			{/if}
		</div>
	{/if}
	<p class="button jsMediaSelectButton jsMediaSelectButton_{@$field->getPrefixedId()}" data-store="{@$field->getPrefixedId()}"{if $field->isImageOnly()} data-display="{@$field->getPrefixedId()}_preview"{/if}>{lang}wcf.media.choose{if $field->isImageOnly()}Image{else}File{/if}{/lang}</p>
	<input type="hidden" name="{@$field->getPrefixedId()}" id="{@$field->getPrefixedId()}"{if $field->getValue()} value="{@$field->getValue()}"{/if}>
	
	<script data-relocate="true">
		{include file='mediaJavaScript'}
		
		require(['WoltLabSuite/Core/Media/Manager/Select'], function(MediaManagerSelect) {
			new MediaManagerSelect({
				buttonClass: 'jsMediaSelectButton_{@$field->getPrefixedId()}',
				{if $field->isImageOnly()}
					dialogTitle: '{lang}wcf.media.chooseImage{/lang}',
					imagesOnly: 1
				{/if}
			});
		});
	</script>
{/if}

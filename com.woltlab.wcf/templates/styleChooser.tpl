<ol class="containerList styleList{if $styleList|count > 4} doubleColumned{/if}">
	{foreach from=$styleList item=style}
		<li data-style-id="{@$style->styleID}">
			<div class="box64">
				<span>
					<img src="{@$style->getPreviewImage()}" alt="">
				</span>
				<div class="details">
					<div class="containerHeadline">
						<h3>{$style->styleName}{if $style->styleID == $__wcf->getStyleHandler()->getStyle()->styleID} <span class="icon icon16 fa-check-circle" title="{lang}wcf.style.currentStyle{/lang}"></span>{/if}</h3>
					</div>
					{if $style->styleDescription}<small>{lang}{@$style->styleDescription}{/lang}</small>{/if}
				</div>
			</div>
		</li>
	{/foreach}
</ol>
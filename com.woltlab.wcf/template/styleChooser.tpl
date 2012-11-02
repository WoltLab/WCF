<div class="container">
	<ol class="containerList styleList{if $styleList|count > 4} doubleColumned{/if}">
		{foreach from=$styleList item=style}
			<li data-style-id="{@$style->styleID}">
				<div class="box64">
					<span class="framed">
						<img src="{@$style->getPreviewImage()}" alt="" />
					</span>
					<div class="details">
						<hgroup class="containerHeadline">
							<h1>{$style->styleName}</h1>
						</hgroup>
						{if $style->styleDescription}<small>{$style->styleDescription}</small>{/if}
					</div>
				</div>
			</li>
		{/foreach}
	</ol>
</div>
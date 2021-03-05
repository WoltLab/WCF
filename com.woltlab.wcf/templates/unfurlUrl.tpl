{if $object->status == "SUCCESSFUL"}
	<div class="unfurlCard {if $object->imageType == 'COVER' && !$object->getImageUrl()|empty}unfurlLargeContentImage{elseif $object->imageType == 'SQUARED' && !$object->getImageUrl()|empty}unfurlSquaredContentImage{/if}">
		<a {anchorAttributes url=$object->url appendClassname=false isUgc=true}>
			<div{if !$object->getImageUrl()|empty} style="background-image: url('{$object->getImageUrl()}')"{/if}></div>
			<div class="unfurlInformation">
				<div class="urlTitle">{$object->title}</div>
				<div class="urlDescription">{$object->description}</div>
				<div class="urlHost">{$object->getHost()}</div>
			</div>
		</a>
	</div>
{else}
	<a {anchorAttributes url=$object->url isUgc=true}>{$object->url}</a>
{/if}
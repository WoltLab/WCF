{if $object->status == "SUCCESSFUL"}
	<div class="unfurlCard {if $object->imageType == 'COVER' && !$object->getImageUrl()|empty}unfurlLargeContentImage{elseif $object->imageType == 'SQUARED' && !$object->getImageUrl()|empty}unfurlSquaredContentImage{/if}">
		<a href="{$object->url}"{if EXTERNAL_LINK_REL_NOFOLLOW || EXTERNAL_LINK_TARGET_BLANK} rel="{if EXTERNAL_LINK_REL_NOFOLLOW}nofollow{/if}{if EXTERNAL_LINK_REL_NOFOLLOW && EXTERNAL_LINK_TARGET_BLANK} {/if}{if EXTERNAL_LINK_TARGET_BLANK}noopener noreferrer{/if}"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>
			<div{if !$object->getImageUrl()|empty} style="background-image: url('{$object->getImageUrl()}')"{/if}></div>
			<div class="unfurlInformation">
				<div class="urlTitle">{$object->title}</div>
				<div class="urlDescription">{$object->description}</div>
				<div class="urlHost">{$object->getHost()}</div>
			</div>
		</a>
	</div>
{else}
	<a href="{$object->url}" class="externalURL" {if EXTERNAL_LINK_REL_NOFOLLOW || EXTERNAL_LINK_TARGET_BLANK} rel="{if EXTERNAL_LINK_REL_NOFOLLOW}nofollow{/if}{if EXTERNAL_LINK_REL_NOFOLLOW && EXTERNAL_LINK_TARGET_BLANK} {/if}{if EXTERNAL_LINK_TARGET_BLANK}noopener noreferrer{/if}"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{$object->url}</a>
{/if}
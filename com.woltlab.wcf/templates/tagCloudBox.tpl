{hascontent}
	<ul class="tagList">
		{content}
			{foreach from=$tags item=tagObj}
				<li><a href="{link controller='Tagged' object=$tagObj}{if !$taggableObjectType|empty}objectType={@$taggableObjectType}{/if}{/link}" rel="tag" class="tagWeight{@$tagObj->getWeight()}">{$tagObj->name}</a></li>
			{/foreach}
		{/content}
	</ul>
{/hascontent}
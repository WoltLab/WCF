<div id="{$node->getPrefixedId()}Container"{if !$node->checkDependencies()} style="display: none;"{/if}>
	{unsafe:$html}
</div>

{include file='shared_formFieldDependencies' field=$node}

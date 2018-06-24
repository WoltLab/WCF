require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Value'], function(ValueFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new ValueFieldDependency(
		'{@$dependency->getDependentNode()->getPrefixedId()}Container',
		'{@$dependency->getField()->getPrefixedId()}'
	).values([ {implode from=$dependency->getValues() item=dependencyValue}'{$dependencyValue|encodeJS}'{/implode} ])
	.negate({if $dependency->isNegated()}true{else}false{/if});
});

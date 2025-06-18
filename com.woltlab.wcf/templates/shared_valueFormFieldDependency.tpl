require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Value'], function(ValueFieldDependency) {
	// dependency '{unsafe:$dependency->getId()|encodeJS}'
	new ValueFieldDependency(
		'{unsafe:$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{unsafe:$dependency->getField()->getPrefixedId()|encodeJS}'
	).values([ {implode from=$dependency->getValues() item=dependencyValue}'{unsafe:$dependencyValue|encodeJS}'{/implode} ])
	.negate({if $dependency->isNegated()}true{else}false{/if});
});

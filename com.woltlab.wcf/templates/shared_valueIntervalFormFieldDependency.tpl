require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/ValueInterval'], ({ ValueInterval }) => {
	// dependency '{unsafe:$dependency->getId()}'
	new ValueInterval(
		'{unsafe:$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{unsafe:$dependency->getField()->getPrefixedId()|encodeJS}'
	)
	.minimum({if $dependency->getMinimum() !== null}{$dependency->getMinimum()}{else}null{/if})
	.maximum({if $dependency->getMaximum() !== null}{$dependency->getMaximum()}{else}null{/if});
});

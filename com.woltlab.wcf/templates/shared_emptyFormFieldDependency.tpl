require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Empty'], function(EmptyFieldDependency) {
	// dependency '{unsafe:$dependency->getId()|encodeJS}'
	new EmptyFieldDependency(
		'{unsafe:$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{unsafe:$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});

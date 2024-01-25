require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Empty'], function(EmptyFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new EmptyFieldDependency(
		'{@$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{@$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});

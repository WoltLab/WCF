require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/NonEmpty'], function(NonEmptyFieldDependency) {
	// dependency '{unsafe:$dependency->getId()}'
	new NonEmptyFieldDependency(
		'{unsafe:$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{unsafe:$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});

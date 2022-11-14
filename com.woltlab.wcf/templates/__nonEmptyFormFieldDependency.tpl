require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/NonEmpty'], function(NonEmptyFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new NonEmptyFieldDependency(
		'{@$dependency->getDependentNode()->getPrefixedId()|encodeJS}Container',
		'{@$dependency->getField()->getPrefixedId()|encodeJS}'
	);
});

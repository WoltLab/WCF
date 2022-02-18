require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/NonEmpty'], function(NonEmptyFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new NonEmptyFieldDependency(
		'{@$dependency->getDependentPrefixedId()}',
		'{@$dependency->getField()->getPrefixedId()}'
	);
});

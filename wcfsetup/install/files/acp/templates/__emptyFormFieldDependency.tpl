require(['WoltLabSuite/Core/Form/Builder/Field/Dependency/Empty'], function(EmptyFieldDependency) {
	// dependency '{@$dependency->getId()}'
	new EmptyFieldDependency(
		'{@$dependency->getDependentPrefixedId()}',
		'{@$dependency->getField()->getPrefixedId()}'
	);
});

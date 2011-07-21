<?php
namespace wcf\data;

interface IDatabaseObjectProcessor {
	public function __construct(DatabaseObject $object);
	public function __get($name);
	public function __isset($name);
	public function __call($name, $arguments);
}

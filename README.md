# Loose Schema Navigator

[![Build Status](https://travis-ci.org/mrubiosan/loose-schema-navigator.svg?branch=master)](https://travis-ci.org/mrubiosan/loose-schema-navigator) [![Maintainability](https://api.codeclimate.com/v1/badges/d75c48caef446238c68c/maintainability)](https://codeclimate.com/github/mrubiosan/loose-schema-navigator/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/d75c48caef446238c68c/test_coverage)](https://codeclimate.com/github/mrubiosan/loose-schema-navigator/test_coverage)

## Example usage
```php
$jsonStr = <<<JSONSTR
{
	"foo":"123"
	"bar":{
		"baz": true,
		"buzz: "{\"abc\":\"xyz\"}"
	}
}
JSONSTR;

$nav = new Navigator($data);

$nav->foo->int() // 123
$nav->foo->string() // "123"
$nav->missingProp->int() // 0
$nav->misisngProp->int(-1) // -1
$nav->bar->baz->int() // 1
$nav->bar->baz->bool() // true
$nav->bar->buzz->string() //  "{"abc":"xyz"}"
$nav->bar->buzz->object() //  {"abc":"xyz"}
$nav->bar->buzz->abc->string() // "xyz"
```

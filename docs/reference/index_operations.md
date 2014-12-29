#Introduction

Here you will find the index API for manage an index in elasticsearch.

Index operations includes so far:

* Create
* Delete
* Mappings

*The Index facade is for Stretchy as Schema Builder is for Laravel.*

# Create an index

To create a basic index just do the following:
```php
Index::create('foo');
```

If you want to specify shards and replicas:
```php
Index::create('foo', function($index)
	{
		$index->shards(5);
		$index->replicas(1);
	});
```

You should put an index creation and deletion in a common laravel migration.

# Delete an index

```php
Index::delete('foo');
```

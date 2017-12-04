# PDODB


## Requirements
- PHP 7.0 or above


## Usage
```php
use xy2z\PDODB\PDODB;

$pdo = new PDODB('database_name', 'user', 'password');

// Change default database to 'test'.
$pdo->use('test');

// Query
$result = $pdo->query("DELETE FROM tablename WHERE name LIKE :name", array(':name' => '%del%'));

// Select query
$data = $pdo->select("SELECT * FROM tablename");
$data = $pdo->select("SELECT * FROM tablename LIMIT :limit", array('limit' => 10));
$data = $pdo->select("SELECT * FROM tablename WHERE name LIKE :name", array('name' => '%Demogorgon%'));

// Select row
$row = $pdo->select_row('SELECT * FROM tablename WHERE id = :id', array(':id' => 4));

// Select a single field
$field = $pdo->select_field('SELECT floatz FROM tablename WHERE id = :id', array(
	':id' => 24
));

// Insert row
$id = $pdo->insert_row('tablename', array(
	'name' => 'The Dude',
	'age' => 234
));

// Insert multiple rows
$insert = $pdo->insert_multi('tablename', array(
	(object) array('name' => "Will", 'age' => '11'),
	array('name' => 'El', 'kewl' => '11'),
));

// Update
$fields = array(
	'name' => 'New Name',
	'age' => '12'
);
$where_id = 24;
$result = $pdo->update('tablename', $fields, array('id' => $where_id));

// Delete rows
$where_id = 14;
$result = $pdo->delete('tablename', array('id' => $where_id));
```

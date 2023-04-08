# Vane

**Vane** is a PHP package that allows you to query a document in nodes, therefore with the typical structure of a noSQL database, but using a familiar and intuitive syntax, through methods inspired by the SQL language.


## Installation


To install the package, run this command from a terminal

```
composer require jaxonrailey/vane
```

## How to use


Once installed, include the namespace

```php
use JaxonRailey\Vane;
```

Initialize an instance of **Vane**

```php
$vane = new Vane();
```

## Example


Consider the following array on which we will do some queries:

```php
$planets = [
    [
        'name'        => 'mercury',
        'temperature' => 440,
        'distance'    => 57910000,
        'satellites'  => 0,
        'atmosphere'  => [],
        'discoverer'  => [
            'firstname'   => 'Giovanni',
            'lastname'    => 'Schiaparelli',
            'nationality' => 'Italian'
        ]
    ],
    [
        'name'        => 'venus',
        'temperature' => 737,
        'distance'    => 108200000,
        'satellites'  => 0,
        'atmosphere'  => ['nitrogen', 'water', 'argon'],
        'discoverer'  => [
            'firstname'   => 'Giovanni',
            'lastname'    => 'Cassini',
            'nationality' => 'Italian'
        ]
    ],
    [
        'name'        => 'earth',
        'temperature' => 15,
        'distance'    => 149600000,
        'satellites'  => 1,
        'atmosphere'  => ['nitrogen', 'oxygen', 'water', 'neon']
    ],
    [
        'name'        => 'mars',
        'temperature' => -63,
        'distance'    => 227940000,
        'satellites'  => 2,
        'atmosphere'  => ['nitrogen', 'argon', 'oxygen', 'water'],
        'discoverer'  => [
            'firstname'   => 'Edward',
            'lastname'    => 'Barnard',
            'nationality' => 'American'
        ]
    ],
    [
        'name'        => 'jupiter',
        'temperature' => -108,
        'distance'    => 778330000,
        'satellites'  => 79,
        'atmosphere'  => ['hydrogen', 'helium', 'methane'],
        'discoverer'  => [
            'firstname'   => 'Galileo',
            'lastname'    => 'Galilei',
            'nationality' => 'Italian'
        ]
    ],
    [
        'name'        => 'saturn',
        'temperature' => -139,
        'distance'    => 1429400000,
        'satellites'  => 82,
        'atmosphere'  => ['hydrogen', 'helium', 'methane'],
        'discoverer'  => [
            'firstname'   => 'Galileo',
            'lastname'    => 'Galilei',
            'nationality' => 'Italian'
        ]
    ],
    [
        'name'        => 'uranus',
        'temperature' => -197,
        'distance'    => 2870990000,
        'satellites'  => 27,
        'atmosphere'  => ['hydrogen', 'helium', 'methane', 'water'],
        'discoverer'  => [
            'firstname'   => 'William',
            'lastname'    => 'Herschel',
            'nationality' => 'German-British'
        ]
    ],
    [
        'name'        => 'neptune',
        'temperature' => -201,
        'distance'    => 4504300000,
        'satellites'  => 14,
        'atmosphere'  => ['hydrogen', 'helium', 'methane', 'water'],
        'discoverer'  => [
            'firstname'   => 'Urbain',
            'lastname'    => 'Le Verrier',
            'nationality' => 'French'
        ]
    ]
];
```

## Basic use

#### Select

```php
$vane->select('*');
$vane->from('planet');
$rows = $vane->rows();
```

#### Insert

```php
$vane->from('planet');
$vane->save($planets);
```

#### Update

```php
$vane->from('planet');
$vane->where('temperature', '>', 0);
$vane->save(['star' => 'Sun']);
```

#### Delete

```php
$vane->from('planet');
$vane->where('temperature', '>', 0);
$vane->delete();
```

#### Truncate

```php
$vane->from('planet');
$vane->truncate();
```

## Advanced use

Select elements based on whether a given value is contained in a property of type array:

```php
$vane->select('*');
$vane->from('planet');
$vane->contains('atmosphere', 'methane');
$rows = $vane->rows();
```

Select elements based on whether a given value is not contained in a property of type array:

```php
$vane->select('*');
$vane->from('planet');
$vane->contains('atmosphere', 'methane', false);
$rows = $vane->rows();
```

Select elements based on whether a given property exists:

```php
$vane->select('*');
$vane->from('planet');
$vane->exists('discoverer');
$rows = $vane->rows();
```

Select elements based on whether a given property does not exist:

```php
$vane->select('*');
$vane->from('planet');
$vane->exists('discoverer', false);
$rows = $vane->rows();
```

Select elements that have more than x elements in an array property:

```php
$vane->select('*');
$vane->from('planet');
$vane->counter('atmosphere', '>', 3);
$rows = $vane->rows();
```

Select a single item based on its identifier:

```php
$vane->select('*');
$vane->from('planet');
$row = $vane->id('<id-of-element>');
```

The select statement can also accept individual properties:

```php
$vane->select('name', 'distance');
$vane->from('planet');
$rows = $vane->rows();
```

nested properties can be used via dot notation:

```php
$vane->select('discoverer.firstname');
$vane->from('planet');
$vane->where('discoverer.nationality', '=', 'Italian');
$rows = $vane->rows();
```

## Sugar syntax

In both the counter method and the where method, you can pass as few as two parameters, the property name and the value to compare, thus suppressing the condition symbol. In this case it means that the condition operator is the equal sign (=)

```php
$vane->where('name', 'mercury');
```

is equivalent to:

```php
$vane->where('name', '=', 'mercury');
```

The same is true for the counter method:

```php
$vane->counter('atmosphere', 3);
```

is equivalent to:

```php
$vane->counter('atmosphere', '=', 3);
```

:star: **If you liked what I did, if it was useful to you or if it served as a starting point for something more magical let me know with a star** :green_heart:

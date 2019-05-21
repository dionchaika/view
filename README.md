# View
The PHP View Engine

## Requirements
1. PHP 7.1.3 or higher

## Installation
```bash
composer require dionchaika/view:dev-master
```

```php
<?php

require_once 'vendor/autoload.php';
```

## Configuration
```php

use Dionchaika\View\View;

$view = new View(
    '/path/to/views',
    '/optional/path/to/compiled/views'
);
```

## Basic usage
1. Rendering views:
```php

//
// Will render a view file
// /path/to/views/home/index.php
// or /path/to/views/home/index.html
//
echo $view->render('home.index');

//
// Passing parameters to the view:
//
echo $view->render('home.index', ['lang' => 'en', 'title' => 'Home Page']);
```

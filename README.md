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
<?php

use Dionchaika\View\View;

$view = new View(
    '/path/to/views',
    '/optional/path/to/compiled/views'
);
```

## Basic usage
1. Rendering views:
```php
<?php

//
// Will render a view file
// with name /path/to/views/home/index.php
// or with name /path/to/views/home/index.html:
//
echo $view->render('home.index');

//
// Passing parameters to the view:
//
echo $view->render('home.index', ['lang' => 'en', 'title' => 'Home Page']);
```

2. Using comments:
```html
## This comment
    will not be rendered into the HTML ##
<h1>Welcome!</h1>
```

3. Using placeholders:
```html
<div class="user">
    <div class="name">{{ $user['name'] }}</div>
    <div class="email">{{ $user['email'] }}</div>
</div>
```

4. Using conditions:
```html
## If condition ##
<div class="container">
    @if $error
        <div class="alert alert-danger">
            {{ $message }}
        </div>
    @endif

    @if $auth
        <div class="alert alert-success">
            You are logged in!
        </div>
    @else
        <div class="alert alert-danger">
            You are not logged in!
        </div>
    @endif

    @if 404 === $status
        <div class="alert alert-danger">
            Page not found!
        </div>
    @elseif 500 === $status
        <div class="alert alert-danger">
            Internal server error!
        </div>
    @endif
</div>
```

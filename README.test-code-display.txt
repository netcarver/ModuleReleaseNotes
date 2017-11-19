# Code Dispay Test Document

This file includes a number of code examples that should all be displayed properly in both formatted and raw versions.

## Inline Examples

- ```$this->version();```
- ```<a href='#'>$this->version();</a>```
- ```<a href='#'><?=$this->version();?></a>```
- ```<a href='#'><?=$this->version();?></a>```


## Indented Code Blocks

    echo <<<HTML
    <h1>Hello World</h1>
    HTML;


## Fenced Blocks

PHP...

```php
echo <<<HTML
<h1>Hello World</h1>
HTML;
```

HTML...

```html
<!-- CSS Stylesheet -->
<link rel="stylesheet" type="text/css" href="<?php echo AllInOneMinify::CSS('css/stylesheet.css'); ?>">

<!-- LESS file -->
<link rel="stylesheet" type="text/css" href="<?php echo AllInOneMinify::CSS('css/stylesheet.less'); ?>">
```

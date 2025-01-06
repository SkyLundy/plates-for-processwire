
# Wire Extension

The Wire Extension provides easy access to ProcessWire utilities that would otherwise need to be instantiated. All objects are memoized except for `wireArray()` which returns a new instance each time

```php
<!-- Get an instance of WireRandom -->

<p>Here is a random string <?=$this->wireRandom()->alphanumeric()?></p>

<!-- Get an instace of WireTextTools -->

<p><?=$this->wireTextTools()->truncate($page->text_field, 250)?></p>

<!-- Create a new WireArray instance -->

<p>Buildings taller than 50 feet:</p>
<?php foreach ($this->wireArray($somePage, $anotherPage, $lastPage)->filter('height>=50') as $building): ?>
  <p><?=$building->title?></p>
<?php endforeach ?>
```

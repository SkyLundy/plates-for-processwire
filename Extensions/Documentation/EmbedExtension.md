# Embed Extension

The Plates for ProcessWire embed extension adds more tools for template and component reusability. It builds on the layout and insert/fetch functionality of Plates by providing additional options for including markup from other files in your templates. You may also capture chunks of markup for reuse in the same template.

## Emnbedding

Embedding merges the functionality of the `insert()` and `layout()` functions provided by Plates. While some extensibility in Plates relies on [stacking layouts](https://platesphp.com/templates/layouts/), embedding lets you use individual templates with regular variables that act as "sections".

Here is an example of a template containing markup for a modal. The markup is exactly the same as any other template that can be included using the `insert()` or `fetch()` methods in Plates.

```php
<!-- /site/templates/components/modal.plates.php -->
<?php $width ??= null; ?>
<div class="modal <?=$size == 'lg' ? 'w-full' : 'w-1/3' ?>">
  <button class="close-modal">
    <img src="images/nav_burger.svg" alt="Close modal">
  </button>
  <header>
    <?=$header?>
  </header>
  <div>
    <?=$body?>
  </div>
</div>
```

Plates allows you to use and reuse this modal template by passing data values, but this is limited to the values passed in the data array which may limit flexibility.

```php
<?=$this->insert('components::modal', [
  'header' => 'Welcome to our website',
  'body' => 'Thanks for visiting. if you have any questions feel free to contact us. We are open Monday through Friday'
])?>
```

With embedding, it is possible to add additional complexity without needing to create additional files. Rather than create new files for each modal desired, you can reuse the single modal template without having to use nested layouts.

- `embed('template::name', ['data' => 'value'])` starts an embed and accepts a template name and a data array.
- `startBlock('variableName')` takes a single string argument that matches the name of a variable in the template being embedded
- `stopBlock()` stops the current block. The alias `endBlock()` may also be used if preferred
- `blockValue('variableName', 'Value to insert')` accepts the name of a variable and the value to insert, it operates the same way as passing a value via the data array
- `stopEmbed()` stops the current embed and outputs the embedded content to the page

Here is an example using an embed with the same modal template above.

```php
<?php $this->embed('components::modal', ['size' => 'lg']) ?>
  <?php $this->startBlock('header') ?>
    <h2>Welcome to our website</h2>
  <?php $this->stopBlock() ?>

  <?php $this->startBlock('body') ?>
    <h3>Thanks for visiting.</h3>
    <p>If you have any questions feel free to <a href="mailto:email@domain.com">contact us</a>.</p>
    <p>Our hours are:</p>
    <ul>
      <li>Sunday: Closed</li>
      <li>Monday: 8:OO-17:00</li>
      <li>Tuesday: 8:OO-17:00</li>
      <li>Wednesday: 8:OO-17:00</li>
      <li>Thursday: 8:OO-17:00</li>
      <li>Friday: 8:OO-17:00</li>
      <li>Saturday: 9:OO-15:00</li>
    </ul>
  <?php $this->stopBlock() ?>
<?php $this->stopEmbed() ?>
```

Another example showing the use of `blockValue()`

```php
<?php $this->embed('components::modal') ?>
  <?php $this->blockValue('header', '<h2>Sign up for our newsletter</h2>') ?>

  <?php $this->startBlock('body') ?>
    <form action="/">
      <input type="text" name="email_address" placeholder="you@email.com">
      <input type="submit" name="submit" value="Sign Up">
    </form>
  <?php $this->stopBlock() ?>
<?php $this->stopEmbed() ?>
```

## Capturing

Capturing lets you assign a block of markup to a variable and output as needed.

To begin a capture, assign `$this->capture()` to a variable. End the capture by calling `stop()` or `end()` on that variable.

### Examples
In this example we have an important announcement that must be added in two locations on the page.
```php
<?php $message = $this->capture() ?>
  <h2>Important</h2>
  <p>The last train leaves at <?=$page->departures->last()->time?></p>
<?php $message->stop() ?>

<section><?=$message?></section>
<!-- ...other markup... -->
<section>
  <table class="train-timetable">
    <tr>
      <td>...</td>
      <td>...</td>
      <td>...</td>
    </tr>
  </table>
</section>
<!-- ...other markup... -->
<section><?=$message?></section>
```

Another use case is rendering markup for navigation that may be used more than once, such as a navigation structure that is used in different locations for desktop and mobile menus.
```php
<?php $navMarkup = $this->capture() ?>
  <nav>
    <ul>
      <?php foreach ($pages->children->prepend($pages->get(1)) as $navItem): ?>
        <li>
          <a href="<?=$navItem->url?>"><?=$navItem->title?></a>
        </li>
      <?php endforeach ?>
    </ul>
  </nav>
<?php $navMarkup->stop() ?>

<header>
  <div class="site-navigation">
    <?=$navMarkup?>
  </div>
  <!-- ...other markup... -->
  <div class="mobile-site-navigation">
    <button id="toggle-mobile-nav">
      <img src="images/nav_burger.svg" alt="Toggle mobile navigation">
    </button>
    <div class="mobile-nav-overlay">
      <?=$navMarkup?>
    </div>
  </div>
</header>
```

Using `$this->capture()` returns an instance of a `Capture` object that has additional features

- The `stop()` and `end()` methods accept one or more function names that are executed using the Plates `batch()` method internally before returning the result.
- You may echo the result to the page or return the output by calling `value()` on the capture variable

```php
<?php $example = $this->capture() ?>
  <!-- ...captured markup... -->
<?php $example->stop('stripHtml|trim') ?>

<?php $output = $example->value()?>
```

## Limitations
It is not possible to use `embed()` within a `capture()`, but it is possible to use `capture()` within an `embed()`.
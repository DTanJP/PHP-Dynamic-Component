# PHP-Dynamic-Component
Generates HTML elements with PHP dynamically.
Made and tested with PHP 7.2

Dynamic Component is a base template to create other components.
You can use it by itself:

```
<?php
  require_once("DynamicComponent.php");
  $div = new DynamicComponent();
  $div->data['tag'] = 'div';
  $div->addClass("container");
  $div->addChild("Hello World");
  echo $div->compile();
?>
```
However it's main strength is in creating 1 time components

```
<?php
  require_once("DynamicComponent.php");
  class text extends DynamicComponent {
  
      /** Constructor **/
      public function __construct() {
          $this->data['tag'] = 'p';
      }
  
  }
  class div extends DynamicComponent {
  
      /** Constructor **/
      public function __construct() {
          $this->data['tag'] = 'div';
          $text = new text();
          $text->addChild("Hello World");
          $text->data['id'] = "txt-0";
          $this->addChild($text);
      }
  
  }
  
  $div = new div();
  $text = $div->select(['tag' => 'p']);
  $text[0]->addClass("text-success"); //If you're using bootstrap
  $text = $div->select("#txt-0");
  $text[0]->data['children'] = "Hello World!";
  echo $div->compile();
?>
```

Often time writing html code in php often turns out something like this:

```
$output = "
<div>
  <p id='txt-0'>Hello world!</p>
</div>
";
echo $output;
```
The html code sample looks small and harmless and really easy to read. Until you eventually write massive amounts of it that it turns to spaghetti code that no one wants to approach it. Dynamic Component just breaks it up and makes it easier to manage.

This project is a prototype and will not be updated, but rather to serve as a base/foundation/reference point for future use.

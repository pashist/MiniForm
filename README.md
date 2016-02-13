# MiniForm
Minimalistic PHP forms library

## Basic usage

#####Creating form instance:
```
$form = new \MiniForm\Form();
$form->addInput(['name' => 'someName', 'type' => 'text']); //name is required
$form->addInput(['name' => 'someName2', 'type' => 'text', 'required' => true]);
```
or
```
$form = new \MiniForm\Form([
    'fields' => [
        ['input', ['type' => 'text', 'name' => 'someName' ]],
        ['input', ['type' => 'text', 'name' => 'someName2', 'required' => true ]]
    ]
]);
```

#####Submit data to form:
```
$form->submit(['someName' => 'someValue']);
```
or
```
$form->submit(); //same as $form->submit($_REQUEST); 
```

#####Validate form:
```
$form->validate(); // not neccessary
$form->isValid(); // true/false
$form->getErrors() // ['someName2' => ['this field is required']]
```

#####Display form:
```html
<div>
   <?php echo $form->html() ?>
</div>
```
Or 
```html
<div>
   <?php echo $form ?>
</div>
```
Or show each field separately
```html
<form>
  <div class="form-group">
    <label>Some Label</label>
    <?php echo $form->someName->html() ?> 
  </div>
  <div class="form-group">
      <label>Some Label 2</label>
      <?php echo $form->someName2 ?> 
    </div>
</form>
```

### To be continued ;)
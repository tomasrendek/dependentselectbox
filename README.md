#foowie/dependentselectbox (MIT)#
This control looks like common selectbox, but available choices to select are dependend on value
of another selectbox. Availabe choices could be synchronized by ajax requests, or by `SubmitButton`
when javascript is disabled.

## How to use this control ##

### Registration: ###
```php
\DependentSelectBox\DependentSelectBox::register(); // First parameter of this method denotes name of method to add selectbox into form. Default name is addDependentSelectBox, but short name like addDSelect can be used.
\DependentSelectBox\JsonDependentSelectBox::register();
```

### How to add control into form, or container: ###
```php
$form->addDependentSelectBox('CONTROL_NAME', 'LABEL_OF_CONTROL', PARENT_SELECTBOX, DATA_CALLBACK);
```
for example
```php
$form->addDependentSelectBox('select2', 'Selection 2', $form['select1'], array($this, 'getValuesSelect2'));
// to enable ajax requests, snippet invalidation on values reload is neccessary
if($this->isAjax()) {
	$form['select2']->addOnSubmitCallback(array($this, 'invalidateControl'), 'SNIPPET_NAME');
}
```
And the data method may look like this:
```php
public function getValuesSelect2($form, $dependentSelectBoxName) {
	$select1 = $form['select1']->getValue();
	return $this->databaseContext->table('tableName')->where('dependencyColumn', $select1)->fetchPairs('id', 'name');
}
```
These selectboxes can be chained, or connected in the tree structure.

> Don't forget to attach form in the constructor `$form = new Form($this, $name);`.
> Ignoring this may cause problems with usage of skipFirst method of parent SelectBox !

## Attributes ##

Attributes and methods of `DependentSelectBox`:

`$disableChilds` = deactivate controls with no value or select first by default?
`$disabledHtmlClass` = Html class for disabled control
`$emptyValueTitle` = Title for “not selected yet” value
`$disabledItemTitle` = Title for disabled control
`$autoSelectRootFirstItem` = Should be first value in parent selected by default? (Recommanded)
`refresh()` = Choices reconstruction
`setDisabledValue(array($key => $value))` = Key and value for disabled control
`setLeaveFirstEmpty(true)` = In case of  $disableChilds == true is possible to choice empty value

Attributes and methods of helper class `FormControlDependencyHelper`, that is responsible of
`SubmitButton` creation. This “Load” button is attached on parent control.

`$buttonSuffix` = Suffix of button name in addition of control name
`$controlClass` = HTML class of control. Class of button is created by $controlClass.$buttonSuffix
`$buttonText` = …
`$buttonPosition` = Button position, see class constants

It is possible to pass instance of `FormControlDependencyHelper` instead of `SelectBox` in the constructor.

Dependency can be set on one or more controls. Using multi-dependency can be achieved by passing array of these controls.
```php
$form->addDependentSelectBox("select3", "Selection 3", array($form["select1"], $form["select2"]), array($this, "getValuesSelect3"));
```
## JavaScript – dependencies ##

There are attributes `controlClass` and `buttonSuffix` in the `jquery.nette.dependencyselectbox.js` file
that must same as in `FormControlDependencyHelper`. The `hideSubmits` function should hide the
submit buttons. The whole table rows are hidden by default for forms rendered by
ConventionalRenderer. In case of maual rendering is necessary to change this behavior. To hide
buttons also on ajax requests is required to append one line into addon [Ajax with jQuery](http://addons.nette.org/cs/jquery-ajax). Add
`$.dependentselectbox.hideSubmits();` to the end of the `success` function. This addon also depends
on addon [Ajax forms](http://addons.nette.org/cs/ajax-form).

## `JsonDependentSelectBox` ##

As alternative to `DependentSelectBox` is `JsonDependentSelectBox`, that sends only necessary data
instead of while snippet. Use `$form->addDependentSelectBox` instead of `$form->addJsonDependentSelectBox`
and in the body of the `beforeRender` method add `JsonDependentSelectBox::tryJsonResponse($presenter);`.

---

And now, example of usage. Thanks to Tomáš Votrubek. `BasePresenter.php`
```php
public function startup() {
	parent::startup();
	\DependentSelectBox\JsonDependentSelectBox::register('addJSelect');
}

public function beforeRender() {
	parent::beforeRender();
	\DependentSelectBox\JsonDependentSelectBox::tryJsonResponse($this /*(presenter)*/);
}
```

`MyPresenter.php`

```php
/** * Select helper */
private function getCarModelsByType($form) {
	$id = $form["car_type"]->value;
	$array = $this->models->car->getModelsByType($id); // return array("key" => "name");
	return $array; // array is required as return type
}
/** * Form */
protected function createComponentTestForm($name) {
	$form = new Form($this, $name); // required for full running
	$form->addSelect("car_type", "Car type", array("Opel", "Škoda", "BMW"));
	$form->addJSelect("car_model","Model", $form["car_type"], array($this, "getCarModelsByType"));
	$form->onSubmit[] = array($this, "testFormSent");
	$form->addSubmit("submit", "Save");
	return $form;
}
/** * Save form */
public function testFormSent(Form $form) {
	if ($form["submit"]->isSubmittedBy()) {
		// required to avoid submit form on select change
		$values = $form->values;
		// ...
	}
}
```
Enjoy

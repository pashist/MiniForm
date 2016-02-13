<?php

namespace MiniForm;

use Closure;

class Form
{
    public $submitted = false;
    public $validated = false;

    private $errors = [];
    private $hasErrors = false;
    private $method = 'post';
    private $action = '#';
    private $class = 'form';
    private $enctype = "multipart/form-data";
    private $events = [];

    /**
     * @var Field[]
     */
    private $fields = [];

    public function __construct(array $data = [])
    {
        if (isset($data['fields']) && is_array($data['fields'])) {
            foreach ($data['fields'] as $field) {
                $this->addField(array_shift($field), array_shift($field));
            }
        }
        isset($data['class']) && $this->class = $data['class'];
        isset($data['enctype']) && $this->enctype = $data['enctype'];
        isset($data['action']) && $this->action = $data['action'];
    }

    public function getField($name)
    {
        return isset($this->fields[$name]) ? $this->fields[$name] : null;
    }

    public function removeField($name)
    {
        unset($this->fields[$name]);

        return $this;
    }

    public function addField($nodeName, array $data = [])
    {
        $field                      = $this->createField($nodeName, $data);
        $this->fields[$field->name] = $field;

        return $this;
    }

    public function addInput(array $data = [])
    {
        return $this->addField('input', $data);
    }

    public function addSelect(array $data = [])
    {
        return $this->addField('select', $data);
    }

    public function createField($nodeName, array $data = [])
    {
        if (empty($data['name'])) {
            throw new Exception(Lang::trans('ATTRIBUTE_REQUIRED', 'name'));
        }
        $fieldClassName = __NAMESPACE__ . '\\' . $nodeName;
        isset($data['type']) && $fieldClassName .= $data['type'];
        if (class_exists($fieldClassName) && is_subclass_of($fieldClassName, __NAMESPACE__ . '\\Field')) {
            /**
             * @var $field Field
             */
            $field = new $fieldClassName($data);

            return $field;
        }
        throw new Exception(Lang::trans("INVALID_FIELD_TYPE"));
    }

    public function submit($data = null)
    {
        if (is_null($data)) {
            $data = $_REQUEST;
        }
        if ($data) {
            $this->trigger('submit', $this);
            foreach ($this->fields as $field) {
                $field->submit($data);
            }
            $this->submitted = true;
            $this->trigger('submitted', $this);
        }
    }

    public function addError($text)
    {
        if ($text) {
            $this->errors[] = Lang::trans($text);
        }
    }

    public function validate()
    {
        $this->trigger('validate', $this);
        foreach ($this->fields as $field) {
            $field->validate();
            if ( ! $this->hasErrors && ! $field->isValid()) {
                $this->hasErrors = true;
            }
        }
        $this->validated = true;
        $this->trigger('validated', $this);
    }

    public function isValid()
    {
        if ( ! $this->validated) {
            $this->validate();
        }

        return ! $this->hasErrors;
    }

    public function html()
    {
        $html = "<form method='$this->method' action='$this->action' class='$this->class' enctype='$this->enctype'>";
        $html .= isset($this->errors['form']) ? '<p class="form_error">' . $this->errors['form'] . '</p>' : '';
        foreach ($this->fields as $field) {
            $html .= $field->html();
        }
        $html .= '</form>';

        return $html;
    }

    public function getValues()
    {
        $values = array();
        foreach ($this->fields as $field) {
            $values[$field->name] = $field->value;
        }

        return $values;
    }

    public function setValues(array $values)
    {
        foreach ($this->fields as $field) {
            isset($values[$field->name]) && $field->value = $values[$field->name];
        }
    }

    public function __set($name, $value)
    {
        $this->fields->$name = $value;
    }

    public function __get($name)
    {
        if ( ! array_key_exists($name, $this->fields)) {
            return null;
        }

        return $this->fields[$name];
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function fieldExists($name)
    {
        return array_key_exists($name, $this->fields);
    }

    public function on($eventName, $callable)
    {
        if ( ! is_callable($callable)) {
            throw new Exception(Lang::trans("SECOND_PARAMETER_MUST_BE_CALLABLE"));
        }
        if ( ! isset($this->events[$eventName])) {
            $this->events[$eventName] = [];
        }
        $this->events[$eventName][] = $callable;
    }

    public function trigger($eventName)
    {
        if ( ! isset($this->events[$eventName])) {
            foreach ($this->events[$eventName] as $callable) {
                $args = func_get_args();
                array_shift($args);
                call_user_func_array($callable, $args);
            }
        }
    }
}
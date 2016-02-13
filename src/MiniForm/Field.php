<?php

namespace MiniForm;

class Field
{
    /**
     * @var Form
     */
    protected $form;

    protected $errors = [];
    protected $validators = [];
    protected $description;
    protected $nodeName;
    protected $label;
    protected $template;

    protected static $defaultTemplate = '{field} <span class="label label-danger">{errors}</span>';

    protected $attributes = [];

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
        $this->init();
    }

    protected function init()
    {
        $this->addValidator([$this, 'validateRequired']);
        $this->addValidator([$this, 'validatePattern']);
    }

    public function isValid()
    {
        return empty($this->errors);
    }

    protected function validateRequired()
    {
        $result = false;
        if ($this->required) {
            if ($this->values && ! $this->value) {
                $result = 'SELECT_REQUIRED';
            } elseif ( ! is_array($this->value) && ! strlen($this->value)) {
                $result = 'INPUT_REQUIRED';
            }
        }

        return $result;
    }

    protected function validatePattern()
    {
        $result = false;
        if ($this->pattern && strlen($this->value) && ! preg_match("~$this->pattern~", $this->value)) {
            $result = 'PATTERN_MISMATCH';
        }

        return $result;
    }

    public function __get($name)
    {
        return $this->hasAttribute($name) ? $this->getAttribute($name) : null;
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __isset($name)
    {
        return $this->hasAttribute($name);
    }

    public function __toString()
    {
        return $this->html();
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }
    public function addAttribute($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function validate()
    {
        foreach ($this->validators as $callable) {
            if (is_callable($callable)) {
                $this->addError($callable($this));
            }
        }
    }

    public function addError($error)
    {
        if ($error) {
            $this->errors[] = $error;
        }
    }

    public function clearErrors()
    {
        $this->errors = [];
    }

    public function addValidator($callable)
    {
        $this->validators[] = $callable;

        return $this;
    }

    public function submit($data)
    {
        if ( ! $this->disabled) {
            $this->value = isset($data[$this->name]) ? $data[$this->name] : null;
        }
    }

    public function html()
    {
        return $this->render($this->getTemplate(), $this->templateVars());
    }

    public function nodeHtml()
    {
        return "<$this->nodeName " . $this->getAttributesHtml() . "/>";
    }

    protected function getAttributeHtml($name)
    {
        $html = '';
        if ($this->hasAttribute($name) && $this->getAttribute($name) !== null) {
            $value = $this->getAttribute($name);
            if ($this->isBooleanAttribute($name)) {
                $value && $html = $name . '="' . $name . '"';
            } else {
                $name == 'name' && $this->multiple && $value .= '[]';
                $html = $name . '="' . $value . '"';
            }
        }

        return $html;
    }

    protected function isBooleanAttribute($name)
    {
        return in_array($name, ['disabled', 'required', 'readonly', 'multiple', 'checked', 'selected']);
    }

    protected function getAttributesHtml($exclude = [])
    {
        $html = [];
        foreach ($this->attributes as $name => $value) {
            if (in_array($name, $exclude)) {
                continue;
            }
            $html[] = $this->getAttributeHtml($name);
        }

        return implode(' ', $html);
    }

    public function setNodeName($name)
    {
        $this->nodeName = $name;

        return $this;
    }

    protected function render($template, $replacement)
    {
        $patterns = array_map(function ($val) {
            return '~{' . $val . '}~';
        }, array_keys($replacement));

        return preg_replace($patterns, $replacement, $template);
    }

    protected function templateVars()
    {
        return [
            'field'       => $this->nodeHtml(),
            'label'       => $this->label,
            'description' => $this->description,
            'errors'      => $this->getErrorsAsString(),
            'attributes'  => $this->getAttributesHtml(),
            'nodeName'    => $this->nodeName
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorsAsString($glue = ', ')
    {
        $result = implode($glue, $this->errors);

        return $result;
    }

    public function setTemplate($tpl)
    {
        $this->template = $tpl;
    }

    public function getTemplate()
    {
        return $this->template ?: static::$defaultTemplate;
    }

    public static function setDefaultTemplate($tpl)
    {
        static::$defaultTemplate = $tpl;
    }
}
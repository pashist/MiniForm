<?php

namespace MiniForm;

class Select extends Field
{
    public $values = [];
    public $optionAttributes = [];

    protected function init()
    {
        parent::init();
        $this->addValidator(function () {
            if ($this->multiple && array_diff($this->value, array_keys($this->values))) {
                return Lang::trans('SELECT_INVALID_VALUE');
            }
        });
    }

    public function submit($data)
    {
        if ($this->multiple) {
            $this->value = isset($data[$this->name]) ? (array)$data[$this->name] : [];
        } else {
            parent::submit($data);
        }
    }

    public function nodeHtml()
    {
        $html = '';
        foreach ($this->values as $value => $text) {
            $selected             = "$this->value" == "$value" ? 'selected="selected" ' : null;
            $optionAttributesHtml = '';
            if (array_key_exists($value,
                    $this->optionAttributes) && is_array($this->optionAttributes[$value])
            ) {
                foreach ($this->optionAttributes[$value] as $attributeName => $attributeValue) {
                    $optionAttributesHtml .= "$attributeName=\"$attributeValue\" ";
                }
            }
            $html .= "<option $optionAttributesHtml $selected value=\"$value\">$text</option>";
        }

        $html = '<select ' . $this->getAttributesHtml() . '>' . $html . '</select>';

        return $html;
    }
}
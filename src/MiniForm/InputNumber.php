<?php

namespace MiniForm;

class InputNumber extends InputText
{
    public function init()
    {
        parent::init();
        $this->addValidator(function (Field $field) {
            if (strlen($field->value) && ! is_numeric($field->value)) {
                $field->addError(Lang::trans('NUMERIC_VALUE'));
            } elseif (strlen($field->value) && $field->min && $field->value < $field->min) {
                $field->addError(Lang::trans('MIN_VALUE', $field->min));
            } elseif (strlen($field->value) && $field->max && $field->value > $field->max) {
                $field->addError(Lang::trans('MAX_VALUE', $field->max));
            }
        });
    }
}
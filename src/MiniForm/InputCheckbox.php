<?php
namespace MiniForm;

class InputCheckbox extends Input
{
    public function submit($data)
    {
        if ( ! $this->disabled) {
            $this->checked = array_key_exists($this->name, $data);
        }
    }
}
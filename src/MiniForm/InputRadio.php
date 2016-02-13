<?php

namespace MiniForm;

class InputRadio extends Input
{
    public $values = array();
    public $nodeTemplate = '<label class="radio inline">{node} {title}</label>';
    public $labelClass;

    public function html()
    {
        $html      = '';
        $nodeValue = $this->value;
        foreach ($this->values as $value => $title) {
            $this->value   = $value;
            $this->checked = $nodeValue == $value ? 'checked' : null;
            $html .= $this->render($this->nodeTemplate, ['node' => $this->nodeHtml(), 'title' => $title]);
        }
        $this->value = $nodeValue;

        return $html;
    }
}
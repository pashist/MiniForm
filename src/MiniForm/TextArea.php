<?php
namespace MiniForm;

class TextArea extends Field
{
    public $value;

    public function nodeHtml()
    {
        $html = '<textarea ' . $this->getAttributesHtml() . '>' . $this->value . '</textarea>';

        return $html;
    }
}

<?php

namespace MiniForm;


class InputEmail extends InputText
{
    public $pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$';
}
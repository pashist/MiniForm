<?php
namespace MiniForm;


class InputFile extends Input
{
    public $allowed = [];
    public $value;

    public function submit($data = null)
    {
        $data || $data = $_REQUEST;
        if ( ! $this->disabled) {
            isset($_FILES[$this->name]['name']) && $this->value = $_FILES[$this->name]['name'];
        }
    }

    public function init()
    {
        parent::init();
        $this->addValidator([$this, 'validateExtension']);
        $this->addValidator([$this, 'validateUpload']);
    }

    protected function validateExtension()
    {
        $result = [];
        $values = (array)$this->value;
        foreach ($values as $value) {
            $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
            if ($this->allowed && array_search($ext, $this->allowed) === false) {
                $result[] = Lang::trans('EXTENSION_NOT_ALLOWED');
            }
        }

        return $result;
    }

    protected function validateUpload()
    {
        $errorNames = array(
            UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
            UPLOAD_ERR_FORM_SIZE  => 'UPLOAD_ERR_FORM_SIZE',
            UPLOAD_ERR_PARTIAL    => 'UPLOAD_ERR_PARTIAL',
            UPLOAD_ERR_NO_FILE    => 'UPLOAD_ERR_NO_FILE',
            UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
            UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
            UPLOAD_ERR_EXTENSION  => 'UPLOAD_ERR_EXTENSION'
        );
        $result     = [];
        if ($this->value) {
            $values = (array)$this->value;
            $errors = (array)$_FILES[$this->name]['error'];
            foreach ($values as $i => $value) {
                if (strlen($value) && $errNo = $errors[$i]) {
                    $error    = isset($errNo, $errorNames) ? $errorNames[$errNo] : 'UPLOAD_UNKNOWN_ERROR';
                    $result[] = "[$value] : " . Lang::trans($error);
                }
            }
        }

        return $result;
    }
}
<?php

namespace Pashist\MiniForm;

use Closure;

class Field {
	/**
	 * @var Form
	 */
	protected $form;

	protected $errors = [ ];
	protected $validators = [ ];
	protected $description;
	protected $nodeName;
	protected $label;
	protected $template;

	protected static $defaultTemplate = '{field} <span class="badge badge-danger">{errors}</span>';

	protected $attributes = [ ];

	public function __construct( array $data = [ ] ) {
		foreach ( $data as $key => $val ) {
			$this->$key = $val;
		}
		$this->init();
	}

	protected function init() {
		$this->addValidator( [ $this, 'validateRequired' ] );
		$this->addValidator( [ $this, 'validatePattern' ] );
	}

	public function isValid() {
		return empty( $this->errors );
	}

	protected function validateRequired() {
		$result = false;
		if ( $this->required ) {
			if ( $this->values && ! $this->value ) {
				$result = 'SELECT_REQUIRED';
			} elseif ( ! is_array( $this->value ) && ! strlen( $this->value ) ) {
				$result = 'INPUT_REQUIRED';
			}
		}

		return $result;
	}

	protected function validatePattern() {
		$result = false;
		if ( $this->pattern && strlen( $this->value ) && ! preg_match( "~$this->pattern~", $this->value ) ) {
			$result = 'PATTERN_MISMATCH';
		}

		return $result;
	}

	public function __get( $name ) {
		return $this->hasAttribute( $name ) ? $this->getAttribute( $name ) : null;
	}

	public function __set( $name, $value ) {
		$this->setAttribute( $name, $value );
	}

	public function __isset( $name ) {
		return $this->hasAttribute( $name );
	}

	public function __toString() {
		return $this->html();
	}

	public function hasAttribute( $name ) {
		return array_key_exists( $name, $this->attributes );
	}

	public function setAttribute( $name, $value ) {
		$this->attributes[ $name ] = $value;

		return $this;
	}

	public function getAttribute( $name ) {
		return isset( $this->attributes[ $name ] ) ? $this->attributes[ $name ] : null;
	}

	public function validate() {
		foreach ( $this->validators as $callable ) {
			if ( is_callable( $callable ) ) {
				$this->addError( $callable( $this ) );
			}
		}
	}

	protected function addError( $error ) {
		if ( $error ) {
			$this->errors[] = $error;
		}
	}

	public function addValidator( $callable ) {
		$this->validators[] = $callable;

		return $this;
	}

	public function submit( $data ) {
		if ( ! $this->disabled ) {
			$this->value = isset( $data[ $this->name ] ) ? $data[ $this->name ] : null;
		}
	}

	public function html() {
		return $this->render( $this->getTemplate(), $this->templateVars() );
	}

	public function nodeHtml() {
		return "<$this->nodeName " . $this->getAttributesHtml() . "/>";
	}

	protected function getAttributeHtml( $name ) {
		$html = '';
		if ( $this->hasAttribute( $name ) && $this->getAttribute( $name ) !== null ) {
			$value = $this->getAttribute( $name );
			if ( $this->isBooleanAttribute( $name ) ) {
				$value && $html = $name . '="' . $name . '"';
			} else {
				$name == 'name' && $this->multiple && $value .= '[]';
				$html = $name . '="' . $value . '"';
			}
		}

		return $html;
	}

	protected function isBooleanAttribute( $name ) {
		return in_array( $name, [ 'disabled', 'required', 'readonly', 'multiple', 'checked', 'selected' ] );
	}

	protected function getAttributesHtml( $exclude = [ ] ) {
		$html = [ ];
		foreach ( $this->attributes as $name => $value ) {
			if ( in_array( $name, $exclude ) ) {
				continue;
			}
			$html[] = $this->getAttributeHtml( $name );
		}

		return implode( ' ', $html );
	}

	public function setNodeName( $name ) {
		$this->nodeName = $name;

		return $this;
	}

	protected function render( $template, $replacement ) {
		$patterns = array_map( function ( $val ) {
			return '~{' . $val . '}~';
		}, array_keys( $replacement ) );

		return preg_replace( $patterns, $replacement, $template );
	}

	protected function templateVars() {
		return [
			'field'       => $this->nodeHtml(),
			'label'       => $this->label,
			'description' => $this->description,
			'errors'      => $this->getErrorsAsString(),
			'attributes'  => $this->getAttributesHtml(),
			'nodeName'    => $this->nodeName
		];
	}

	protected function getErrorsAsString() {
		$errors = $this->errors;
		foreach ( $errors as &$error ) {
			if ( is_array( $error ) ) {
				$error = implode( ', ', $error );
			}
		}
		$result = implode( ', ', $errors );

		return $result;
	}

	public function setTemplate( $tpl ) {
		$this->template = $tpl;
	}

	public function getTemplate() {
		return $this->template ?: static::$defaultTemplate;
	}

	public static function setDefaultTemplate( $tpl ) {
		static::$defaultTemplate = $tpl;
	}
}

class Input extends Field {
	public $nodeName = 'input';
}

class InputText extends Input {
}

class InputNumber extends InputText {
	public function init() {
		parent::init();
		$this->addValidator( function ( Field $field ) {
			if ( strlen( $field->value ) && ! is_numeric( $field->value ) ) {
				$field->addError( Lang::trans( 'NUMERIC_VALUE' ) );
			} elseif ( strlen( $field->value ) && $field->min && $field->value < $field->min ) {
				$field->addError( Lang::trans( 'MIN_VALUE', $field->min ) );
			} elseif ( strlen( $field->value ) && $field->max && $field->value > $field->max ) {
				$field->addError( Lang::trans( 'MAX_VALUE', $field->max ) );
			}
		} );
	}
}

class InputPassword extends InputText {
}

class InputHidden extends InputText {

}

class InputEmail extends InputText {
	public $pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$';
}

class InputCheckbox extends Input {
	public function submit( $data ) {
		if ( ! $this->disabled ) {
			$this->checked = array_key_exists( $this->name, $data );
		}
	}
}

class InputSubmit extends Input {
}

class InputButton extends Input {
}

class InputFile extends Input {
	public $allowed = [ ];
	public $value;

	public function submit() {
		if ( ! $this->disabled ) {
			isset( $_FILES[ $this->name ]['name'] ) && $this->value = $_FILES[ $this->name ]['name'];
		}
	}

	public function init() {
		parent::init();
		$this->addValidator( [ $this, 'validateExtension' ] );
		$this->addValidator( [ $this, 'validateUpload' ] );
	}

	protected function validateExtension() {
		$result = [ ];
		$values = (array) $this->value;
		foreach ( $values as $value ) {
			$ext = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );
			if ( $this->allowed && array_search( $ext, $this->allowed ) === false ) {
				$result[] = Lang::trans( 'EXTENSION_NOT_ALLOWED' );
			}
		}

		return $result;
	}

	protected function validateUpload() {
		$errorNames = array(
			UPLOAD_ERR_INI_SIZE   => 'UPLOAD_ERR_INI_SIZE',
			UPLOAD_ERR_FORM_SIZE  => 'UPLOAD_ERR_FORM_SIZE',
			UPLOAD_ERR_PARTIAL    => 'UPLOAD_ERR_PARTIAL',
			UPLOAD_ERR_NO_FILE    => 'UPLOAD_ERR_NO_FILE',
			UPLOAD_ERR_NO_TMP_DIR => 'UPLOAD_ERR_NO_TMP_DIR',
			UPLOAD_ERR_CANT_WRITE => 'UPLOAD_ERR_CANT_WRITE',
			UPLOAD_ERR_EXTENSION  => 'UPLOAD_ERR_EXTENSION'
		);
		$result     = [ ];
		if ( $this->value ) {
			$values = (array) $this->value;
			$errors = (array) $_FILES[ $this->name ]['error'];
			foreach ( $values as $i => $value ) {
				if ( strlen( $value ) && $errNo = $errors[ $i ] ) {
					$error    = isset( $errNo, $errorNames ) ? $errorNames[ $errNo ] : 'UPLOAD_UNKNOWN_ERROR';
					$result[] = "[$value] : " . Lang::trans( $error );
				}
			}
		}

		return $result;
	}
}

class InputRadio extends Input {
	public $values = array();
	public $nodeTemplate = '<label class="radio inline">{node} {title}</label>';
	public $labelClass;

	public function html() {
		$html      = '';
		$nodeValue = $this->value;
		foreach ( $this->values as $value => $title ) {
			$this->value   = $value;
			$this->checked = $nodeValue == $value ? 'checked' : null;
			$html .= $this->render( $this->nodeTemplate, [ 'node' => $this->nodeHtml(), 'title' => $title ] );
		}
		$this->value = $nodeValue;

		return $html;
	}
}

class Select extends Field {
	public $values = [ ];
	public $optionAttributes = [ ];

	protected function init() {
		parent::init();
		$this->addValidator( function () {
			if ( $this->multiple && array_diff( $this->value, array_keys( $this->values ) ) ) {
				return Lang::trans( 'SELECT_INVALID_VALUE' );
			}
		} );
	}

	public function submit( $data ) {
		if ( $this->multiple ) {
			$this->value = isset( $data[ $this->name ] ) ? (array) $data[ $this->name ] : [ ];
		} else {
			parent::submit( $data );
		}
	}

	public function nodeHtml() {
		$html = '';
		foreach ( $this->values as $value => $text ) {
			$selected             = "$this->value" == "$value" ? 'selected="selected" ' : null;
			$optionAttributesHtml = '';
			if ( array_key_exists( $value,
					$this->optionAttributes ) && is_array( $this->optionAttributes[ $value ] )
			) {
				foreach ( $this->optionAttributes[ $value ] as $attributeName => $attributeValue ) {
					$optionAttributesHtml .= "$attributeName=\"$attributeValue\" ";
				}
			}
			$html .= "<option $optionAttributesHtml $selected value=\"$value\">$text</option>";
		}

		$html = '<select ' . $this->getAttributesHtml() . '>' . $html . '</select>';

		return $html;
	}
}

class TextArea extends Field {
	public $value;

	public function nodeHtml() {
		$html = '<textarea ' . $this->getAttributesHtml() . '>' . $this->value . '</textarea>';

		return $html;
	}
}


class Form {
	public $submitted = false;
	public $validated = false;

	private $errors = [ ];
	private $hasErrors = false;
	private $method = 'post';
	private $action = '#';
	private $class = 'form';
	private $enctype = "multipart/form-data";
	private $events = [ ];

	/**
	 * @var Field[]
	 */
	private $fields = [ ];

	public function __construct( array $data = [ ] ) {
		if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {
			foreach ( $data['fields'] as $field ) {
				$this->addField( array_shift( $field ), array_shift( $field ) );
			}
		}
		isset( $data['class'] ) && $this->class = $data['class'];
		isset( $data['enctype'] ) && $this->enctype = $data['enctype'];
		isset( $data['action'] ) && $this->action = $data['action'];
	}

	public function getField( $name ) {
		return isset( $this->fields[ $name ] ) ? $this->fields[ $name ] : null;
	}

	public function removeField( $name ) {
		unset( $this->fields[ $name ] );

		return $this;
	}

	public function addField( $nodeName, array $data = [ ] ) {
		$field                        = $this->createField( $nodeName, $data );
		$this->fields[ $field->name ] = $field;

		return $this;
	}

	public function addInput( array $data = [ ] ) {
		return $this->addField( 'input', $data );
	}

	public function addSelect( array $data = [ ] ) {
		return $this->addField( 'select', $data );
	}

	public function createField( $nodeName, array $data = [ ] ) {
		if ( empty( $data['name'] ) ) {
			throw new Exception( Lang::trans( 'ATTRIBUTE_REQUIRED', 'name' ) );
		}
		$fieldClassName = __NAMESPACE__ . '\\' . $nodeName;
		isset( $data['type'] ) && $fieldClassName .= $data['type'];
		if ( class_exists( $fieldClassName ) && is_subclass_of( $fieldClassName, __NAMESPACE__ . '\\Field' ) ) {
			/**
			 * @var $field Field
			 */
			$field = new $fieldClassName( $data );

			return $field;
		}
		throw new Exception( Lang::trans( "INVALID_FIELD_TYPE" ) );
	}

	public function submit( $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_REQUEST;
		}
		if ( $data ) {
			$this->trigger( 'submit', $this );
			foreach ( $this->fields as $field ) {
				$field->submit( $data );
			}
			$this->submitted = true;
			$this->trigger( 'submitted', $this );
		}
	}

	public function addError( $text ) {
		if ( $text ) {
			$this->errors[] = Lang::trans( $text );
		}
	}

	public function validate() {
		$this->trigger( 'validate', $this );
		foreach ( $this->fields as $field ) {
			$field->validate();
			if ( ! $this->hasErrors && ! $field->isValid() ) {
				$this->hasErrors = true;
			}
		}
		$this->validated = true;
		$this->trigger( 'validated', $this );
	}

	public function isValid() {
		if ( ! $this->validated ) {
			$this->validate();
		}

		return ! $this->hasErrors;
	}

	public function html() {
		$html = "<form method='$this->method' action='$this->action' class='$this->class' enctype='$this->enctype'>";
		$html .= isset( $this->errors['form'] ) ? '<p class="form_error">' . $this->errors['form'] . '</p>' : '';
		foreach ( $this->fields as $field ) {
			$html .= $field->html();
		}
		$html .= '</form>';

		return $html;
	}

	public function getValues() {
		$values = array();
		foreach ( $this->fields as $field ) {
			$values[ $field->name ] = $field->value;
		}

		return $values;
	}

	public function setValues( array $values ) {
		foreach ( $this->fields as $field ) {
			isset( $values[ $field->name ] ) && $field->value = $values[ $field->name ];
		}
	}

	public function __set( $name, $value ) {
		$this->fields->$name = $value;
	}

	public function __get( $name ) {
		if ( ! array_key_exists( $name, $this->fields ) ) {
			return null;
		}

		return $this->fields[ $name ];
	}

	public function getFields() {
		return $this->fields;
	}

	public function fieldExists( $name ) {
		return array_key_exists( $name, $this->fields );
	}

	public function on( $eventName, $callable ) {
		if ( ! is_callable( $callable ) ) {
			throw new Exception( Lang::trans( "SECOND_PARAMETER_MUST_BE_CALLABLE" ) );
		}
		if ( ! isset( $this->events[ $eventName ] ) ) {
			$this->events[ $eventName ] = [ ];
		}
		$this->events[ $eventName ][] = $callable;
	}

	public function trigger( $eventName ) {
		if ( ! isset( $this->events[ $eventName ] ) ) {
			foreach ( $this->events[ $eventName ] as $callable ) {
				$args = func_get_args();
				array_shift( $args );
				call_user_func_array( $callable, $args );
			}
		}
	}
}
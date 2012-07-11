<?php
class C3op_Util_FormFieldValueSetter {
    
    public static function SetValueToFormField(Zend_Form $form, $fieldName, $value)
    {
        try {
            $field = $form->getElement($fieldName);        
        } catch (Exception $e) {
            throw new C3op_Util_FormFieldValueSetterException("Cant find %s element", $fieldName);
        }
        $field->setValue($value);
    }
}


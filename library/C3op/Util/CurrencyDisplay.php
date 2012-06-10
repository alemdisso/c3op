<?php
class C3op_Util_CurrencyDisplay {

    
    
    public static function FormatCurrency($rawValue)
    {

        $currency = new Zend_Currency(
            array(
                'value' => $rawValue,
            )
        );

        
        return $currency;
    }
}


<?php

class C3op_Projects_AreaActivityTypes {
    
    private static $titles = array(
        C3op_Projects_AreaActivityConstants::AREA_SOCIAL_POLICIES => "Políticas sociais e melhoria dos gastos públicos",
        C3op_Projects_AreaActivityConstants::AREA_INEQUALITY => "Desigualdade e pobreza",
        C3op_Projects_AreaActivityConstants::AREA_LABOR_MARKET => "Mercado de trabalho, emprego e rendimento",
        C3op_Projects_AreaActivityConstants::AREA_LOCAL_DEVELOPMENT => "Desenvolvimento local e microempreendimento",
        C3op_Projects_AreaActivityConstants::AREA_EDUCATION => "Educação, recursos humanos e eqüidade",
        C3op_Projects_AreaActivityConstants::AREA_METROPOLITAN_QUESTION => "A questão metropolitana",
        C3op_Projects_AreaActivityConstants::AREA_CORPORATE_SOCIAL_RESPONSABILITY => "Responsabilidade social corporativa",
    );
    
    public static function TitleForType($type)
    {
            switch ($type) {
                case C3op_Projects_AreaActivityConstants::AREA_SOCIAL_POLICIES:
                case C3op_Projects_AreaActivityConstants::AREA_INEQUALITY:
                case C3op_Projects_AreaActivityConstants::AREA_LABOR_MARKET:
                case C3op_Projects_AreaActivityConstants::AREA_LOCAL_DEVELOPMENT:
                case C3op_Projects_AreaActivityConstants::AREA_EDUCATION:
                case C3op_Projects_AreaActivityConstants::AREA_METROPOLITAN_QUESTION:
                case C3op_Projects_AreaActivityConstants::AREA_CORPORATE_SOCIAL_RESPONSABILITY:
                    return self::$titles[$type];
                    break;
                
                default:
                    return "Tipo desconhecido";
                    break;
            }
    }
    
    public static function AllTitles()
    {
        return self::$titles;        
    }   
}
<?php

class Base
{

    /**
     * Get value of object's accesible attribute
     * 
     * @param attributeName:string
     * - class attribute to retrieve value of 
     * 
     * @return <any>
     */
    function attr( $attributeName )
    {
        if( in_array($attributeName, $this->getPublicAttributeList()) )
            return $this->{ $attributeName };
        else
            return false;
    }

    /**
     * Return an escaped attribute value from the wp_options table
     * 
     * @param value:string
     * @return None
     */
    function getCleanOption( $fieldName )
    {
        return esc_attr( get_option($fieldName) );
    }

    /**
     * @override
     * 
     * @param None
     * 
     * @return array
     */
    function getPublicAttributeList()
    {
        return [];
    }

}
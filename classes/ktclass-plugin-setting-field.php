<?php

class PluginSettingField extends Base
{

    /**
     * Initialize a PluginSettingField
     * 
     * @param fieldName:string
     * - field name to identify field in forms and wp_option table
     * @param labelName:string
     * - label to display in field's form field
     * @param default:<any>
     * - default value to store in wp_option table
     * @param htmlSetterCallback:<string|array>
     * - function name called from a Plugin class that sets the html content to display for field
     * @param sanitizeCallback:<string|array>
     * - function name called that sanitizes input for field
     * 
     * @return None
     */
    function __construct( $plugin, $fieldName, $labelName, $default, $sectionName, $sanitizeCallback, $errorMessage, $args = [] )
    {
        error_log( 
            $fieldName.' '.
            $labelName.' '.
            $default.' '.
            $sectionName.' '
        );
        $this->args               = $args;
        $this->default            = $default;
        $this->fieldName          = $fieldName;
        $this->labelName          = $labelName;
        $this->sanitizeCallback   = $sanitizeCallback;
        $this->errorMessage       = $errorMessage;
        $this->addSettingsToPlugin( $plugin, $sectionName );
    }

    /**
     * List of class' accessible attributes
     * 
     * @override
     * @param None
     * @return array:string
     */
    function getPublicAttributeList()
    {
        return [ 
            'args',
            'default',
            'errorMessage',
            'fieldName',
            'labelName', 
            'sanitizeCallback'
        ];
    }

    /**
     * 1. Create settings field interface
     * 2. Register settings in wp_options table
     * 
     * @param plugin 
     *  - plugin object containing attributes: 
     *      pageUrl:<string>, 
     *      sectionName:<string>, 
     *      groupName:<string> 
     *  - plugin object containing htmlSetterCallback function  
     * 
     * @return None 
     */
    function addSettingsToPlugin( $plugin, $sectionName )
    {
        
        $fieldName = $this->attr( 'fieldName' );
        
        // Add settings field to plugin
        add_settings_field( 
            $fieldName,                                     // Field name 
            $this->attr( 'labelName' ),                     // Label
            [$this, 'setHtml'],                             // Sets field html
            $plugin->getSettingsPageUrl(),                       // Slug/route
            $sectionName,                                   // section to add field to
            $this->attr( 'args' )                           // args
        );

        // Add to field to wp_options table
        $sanitizer = $this->attr( 'sanitizeCallback' );
        if( is_array($sanitizer) ){
            $sanitizer = [$this, $sanitizer[0] ];
        }
        register_setting( 
            $plugin->getGroupName(),                                    // Group Name
            $fieldName,                                                 // Field 
            [
                'sanitize_callback' => $sanitizer,                       // Function to sanitize input
                'default' => $this->attr( 'default' )                     // Default value
            ]
        );

    }

    /**
     * Sets the html for the field
     * 
     * @overriden
     */
    function setHtml(){}

}

class SelectSettingField extends PluginSettingField
{
   
    /**
     * Sets the html content for a select field
     * 
     * @override
     */
    function setHtml()
    {
        $args      = $this->attr( 'args' );
        $fieldName = $this->attr( 'fieldName' );
        $dbValue   = get_option( $fieldName ); 
    ?>
        <select name="<? echo $fieldName ?>">
            <?foreach( $args['options'] as $option ){?>
                <option value="<? echo $option['value']; ?>" <? selected($dbValue, $option['value']); ?>><? echo $option['label']; ?></option>
            <?}?>
        </select>
    <?}

    function sanitize( $input )
    {
        if( $input != '0' && $input != '1' ){
            $fieldName = $this->attr( 'fieldName' );
            add_settings_error(
                $fieldName,
                $fieldName.'_error',
                $this->attr( 'errorMessage' )
            );
            return get_option( $fieldName );
        }
        return $input;
    }

}

class TextSettingField extends PluginSettingField
{

    /**
     * Sets the html content for a input-text field
     * 
     * @override
     */
    function setHtml()
    {
        error_log('reached test');
        $fieldName = $this->attr( 'fieldName' );
        $dbValue   = $this->getCleanOption( $fieldName );
    ?>
        <input type="text" name="<? echo $fieldName; ?>" value="<? echo $dbValue; ?>" />
    <?}

}

class CheckboxSettingField extends PluginSettingField
{

    /**
     * Sets the html content for a input-checkbox field
     * 
     * @override
     */
    function setHtml()
    {
        $fieldName = $this->attr( 'fieldName' );
        $dbValue   = get_option( $fieldName );
    ?>
        <input type="checkbox" name="<? echo $fieldName; ?>" value="1" <? checked($dbValue, 1); ?> />
    <?}

    function sanitize( $input )
    {
        if( $input!=null && $input != '0' && $input != '1' ){
            error_log( $input );
            $fieldName = $this->attr( 'fieldName' );
            add_settings_error(
                $fieldName,
                $fieldName.'_error',
                $this->attr( 'errorMessage' )
            );
            return get_option( $fieldName );
        }
        return $input;
    }

}
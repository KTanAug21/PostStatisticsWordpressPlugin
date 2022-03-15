<?php
/**
 * Plugin Name: Post Statistics Plugin
 * Author: Kathryn Anne Tan
 * Author URI: https://github.com/KTanAug21
 * Version: 1.0
 * Description: Provides statistics of a post
 */
require_once( 'classes/ktclass-base.php' );
require_once( 'classes/ktclass-plugin.php' );
require_once( 'classes/ktclass-plugin-setting-field.php' );

/**
 * Actual Plugin Settings field
 * Actual Plugin Actions 
 */
class PostStatisticsPlugin extends Plugin
{
    /**
     * Add Setting Fields for plugin
     * @override
     * @param None
     * @return None
     */
    function configureSettings()
    {
        // Add section
        $sectionName = 'first_section';
        add_settings_section(
            $sectionName , // section to add field to
            null,
            null, 
            $this->getSettingsPageUrl()
        );

        // Location to show statistics
        $location = new SelectSettingField(
            $this,                                              // Plugin reference
            'psp_location',                                     // Field Name
            'Display Location',                                 // Label
            '0',                                                // Default
            $sectionName,                                       // Section name to show
            ['sanitize'],                                       // Sanitizer
            'Display Location must either be Beginning or End', // Sanitize error message
            [                                                   // args 
                'options'=>[
                    ['value'=>'0','label'=>'Beginning'],
                    ['value'=>'1','label'=>'End'],
                ]
            ] 
        );

        // Headline
        $headline = new TextSettingField(
            $this,                                  // Plugin reference
            'psp_headline',                         // Field Name
            'Headline',                             // Label
            $this->getReadableName(),               // Default
            $sectionName,                           // Section name to show
            'sanitize_text_field',                  // Sanitizer
            null,                                   // Sanitize error message
            []                                      // options
        );

        // WordCount flag
        $wordCountFlag = new CheckboxSettingField(
            $this,                                      // Plugin reference 
            'psp_word_count_flag',                      // Field Name
            'Word Count',                               // Label
            '1',                                        // Default
            $sectionName,                               // Section name to show
            ['sanitize'],                               // Sanitizer,
            'Word count must be enabled or disabled!',  // error message
            []
        );

        // Country Visits flag
        $countryVisits = new CheckboxSettingField(
            $this,                                      // Plugin reference 
            'psp_country_visit_flag',                   // Field Name
            'Visits per Country',                       // Label
            '0',                                        // Default
            $sectionName,                               // Section name to show
            ['sanitize'],                               // Sanitizer,
            'Visits per Country must be enabled or disabled!',  // error message
            []
        );
    }
    
    /**
     * Plugin Main Action
     * 
     * @override
     * @param None
     * @return None
     */
    function addPluginAction()
    {
        add_filter( 'the_content', [$this,'applyAction'] );
    }

    /**
     * Determines if to add statisitcs
     * Determines where to add statistics
     * 
     * @param content 
     * - post content to edit with inclusion of statistics
     * @return string
     */
    protected function applyAction( $content )
    {
        if( 
            (is_main_query() && is_single())
        ){
            
            $include = '';
            if( get_option('psp_word_count_flag') === '1' ){
                $include = '<b>Test Word Count</b><br>';
            }

            if( get_option('psp_country_visit_flag') === '1' ){
                $include .= '<b>Test Country Visit</b>';
            }

            // Beginning
            if( get_option('psp_location', '0') ){
                return $include.'<hr>'.$content;
            }else{
                return $content.'<hr>'.$include;
            }

        }
        return $content;
    }
}

$postStatisticsPlugin = new PostStatisticsPlugin( 'Post Statistics' );




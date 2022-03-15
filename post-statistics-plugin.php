<?php
/**
 * Plugin Name: Post Statistics Plugin
 * Author: Kathryn Anne Tan
 * Author URI: https://github.com/KTanAug21
 * Version: 1.0
 * Description: Provides summary of word count in a content
 */
require_once( 'classes/ktclass-base.php' );
require_once( 'classes/ktclass-plugin-setting-field.php' );

/**
 * Generates strings based on readable name of a plugin
 */
class PluginNameProcessor
{
    private $readableName;
    private $wordList;

    /**
     * @param readableName:string
     * - Human Readable Name, Words space separated, First letter per word capitalized
     */
    function __construct( $readableName )
    {    
        $this->setReadableName( $readableName );
        $this->setWordList();
    }

    /**
     * Concatenates first characters of each word into a string
     * to be used as prefix for db options
     * @param None
     * @return string
     * - sample: acp 
     */
    function getDbPrefix()
    {
        $prefix = '';
        foreach( $this->getWordList() as $word ){
            $prefix .= $word[0];
        }
        return $prefix;
    }
   
    /**
     * Generates group name 
     * @param None
     * @return string
     * - sample: post_statistics_plugin_group
     */
    function getGroupName()
    {
        $groupName = '';
        foreach( $this->getWordList() as $word ){
            $groupName .= $word .'_';
        }
        $groupName .= 'plugin_group';
        return $groupName;
    }

    /**
     * Human readable name of Plugin
     */
    function getReadableName()
    {
        return $this->readableName;
    }
    
    /**
     * Generates settings page title name 
     * @param None
     * @return string
     * - sample: Post Statistics Settings
     */
    function getSettingsPageTitle()
    {
        return $this->getReadableName().' Settings';
    }

    /**
     * Generates settings page url for plugin
     * @param None
     * @return string
     * - sample: post-statistics-settings-page
     */
    function getSettingsPageUrl()
    {
        $pageUrl = '';
        foreach( $this->getWordList() as $word ){
            $pageUrl .= $word .'-';
        }
        $pageUrl .= 'settings-page';
        return $pageUrl;
    }
    
     /**
     * Returns wordList
     * @param None
     * @return array:string
     */
    function getWordList()
    {
        return $this->wordList;
    }

    /**
     * Sets readable name of plugin
     * 
     * @param readableName:string
     * - Words space separated
     * @return None
     */
    function setReadableName( $value )
    {
        $this->readableName = $value;
    }
  
    /**
     * Lists words into an array of string, lowercased
     * @param None
     * @return None
     */
    function setWordList()
    {
        $this->wordList = array_map( 
            'strtolower',
            explode( ' ', $this->getReadableName() )
        );
    }
}

/**
 * Sets up a plugin's attributes, settings page
 */
class Plugin extends Base
{
    private $groupName;
    private $settingsPageTitle;
    private $settingsPageUrl;

    function __construct( $readableName )
    {
        // Attributes
        $nameGenerator = new PluginNameProcessor( $readableName );
        $this->setReadableName( $nameGenerator->getReadableName() );
        $this->setGroupName( $nameGenerator->getGroupName() );
        $this->setSettingsPageUrl( $nameGenerator->getSettingsPageUrl() );
        $this->setSettingsPageTitle( $nameGenerator->getSettingsPageTitle() );

        // Setup
        add_action( 'admin_menu', [$this, 'configureSettingsPage'] );
        add_action( 'admin_init', [$this, 'configureSettings'] );

        // Action
        $this->addPluginAction();
    }

    /**
     * Plugin Main Action
     * 
     * @overriden
     * @param None
     * @return None
     */
    function addPluginAction(){}


    /**
     * Configure html of settings page for this plugin
     * 
     * @param None
     * @return None
     */
    function configureSettingsPage()
    {
        add_options_page(
            $this->getSettingsPageTitle(),
            $this->getReadableName(),
            'manage_options',
            $this->getSettingsPageUrl(),
            [$this, 'showSettingsPageHtml']
        );
    }

    function getGroupName()
    {
        return $this->groupName;
    }

    function getReadableName()
    {
        return $this->readableName;
    }

    function getSettingsPageTitle()
    {
        return $this->settingsPageTitle;
    }
 
    function getSettingsPageUrl()
    {
        return $this->settingsPageUrl;
    }

    protected function setGroupName( $value )
    {
        $this->groupName = $value;
    }

    protected function setReadableName( $value )
    {
        $this->readableName = $value;
    }

    protected function setSettingsPageUrl( $value )
    {
        $this->settingsPageUrl = $value;
    }

    protected function setSettingsPageTitle( $value )
    {
        $this->settingsPageTitle = $value;
    }

    function showReadableName()
    {
        echo $this->getReadableName();
    }

    function showSettingsPageHtml()
    {?>
        <div class="wrap">
            <h1><? $this->showReadableName(); ?></h1>
            <form action="options.php" method="POST">
                <?
                    settings_fields( $this->getGroupName() );
                    do_settings_sections( $this->getSettingsPageUrl() );
                    submit_button();
                ?>
            </form>
        </div>
    <?}
}

/**
 * Actual Plugin Settings field
 * Actual Plugin Actions 
 */
class PostStatisticsPlugin extends Plugin
{
    /**
     * Add Setting Fields for plugin
     * 
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
    function applyAction( $content )
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

$wordCountPlugin = new PostStatisticsPlugin( 'Post Statistics' );




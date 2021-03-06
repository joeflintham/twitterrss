<?php
/**
 * @package TwitterRSS
 * @version 1
 */
/*
Plugin Name: Twitter RSS feeds
Plugin URI: http://www.fathompoint.co.uk/wordpress/plugins/TwitterRSS
Description: This plugin reads Twitter JSON feeds and provides cached RSS feeds of same
Author: Joe Flintham
Version: 1
Author URI: http://www.fathompoint.co.uk
*/

define("FEEDS_PLUGIN_PATH", plugin_dir_path(__FILE__));

require_once(FEEDS_PLUGIN_PATH . "twitteroauth/twitteroauth.php");
require_once(FEEDS_PLUGIN_PATH . "rssWriter.php");
require_once(FEEDS_PLUGIN_PATH . "config.php");

class TwitterRSS {

    private $screen_name;
    private $cacheFolder;
    private $feedfileName;
    private $cacheExpiry;

    function TwitterRSS($data){

        // expected parameters:
            // Int cacheExpiry      - no of mins to wait between cache refresh
            // Bool forceRefresh    - ignore cached version if true
            // String cacheFolder   - replaces default value "./cache"
            // Boolean verbose      - determines whether error messages are echoed
            // String screen_name   - specifies the Twitter account RSS feed
                                    // to be cached / returned
            
            
        $verbose            =   (isset($data["verbose"]) 
                                &&
                                $data["verbose"] == 'true') 
                            ? true 
                            : false;
        
        define ('MISSING_SCREEN_NAME', ($verbose) ? 'No Twitter Screen Name supplied' : '');
        
        $this->screen_name  =   (isset($data["screen_name"]) 
                                && 
                                preg_match("/[a-zA-Z0-9]+/", $data["screen_name"])) 
                            ? $data["screen_name"]
                            : die(MISSING_SCREEN_NAME);
        
        $this->cacheFolder  =   (isset($data["cacheFolder"]) 
                                && 
                                preg_match("/[a-zA-Z0-9]+/", $data["cacheFolder"])) 
                            ? $data["cacheFolder"]
                            :  FEEDS_PLUGIN_PATH . "cache";
        
        $this->cacheExpiry  =   (isset($data["cacheExpiry"]) 
                                && 
                                preg_match("/[a-zA-Z0-9]+/", $data["cacheExpiry"])) 
                            ? $data["cacheExpiry"]
                            :  10;
        
        $this->forceRefresh  =   (isset($data["forceRefresh"]) 
                                && 
                                $data["forceRefresh"]) 
                            ? true
                            : false;
        
        $this->testCache();
    
    }
    
    
    
    
    
     
    function testCache(){

        if (!(is_dir($this->cacheFolder))){
            exec( 
            "mkdir " . $this->cacheFolder );
            exec( 
            "chmod 777 " . $this->cacheFolder );
        }

        $this->feedfileName = $this->cacheFolder . "/" . $this->screen_name . ".rss";
        
        if (!($this->forceRefresh)){
            if (file_exists($this->feedfileName)){
                $lastModifiedTime = filemtime($this->feedfileName);
                $now = time();
                $cacheExpirySeconds = $this->cacheExpiry * 60;
                $cacheAge = $now - $lastModifiedTime;
                
                if ($cacheAge < $cacheExpirySeconds){
    
                    $fileHandle = fopen($this->feedfileName, "r");
                    $rssContent = fread($fileHandle, filesize($this->feedfileName));
                    fclose($fileHandle);
    
                    header("Content-type: text/xml");
                    echo $rssContent;
                    die();
    
                }
            }
        }
                
        $this->pollTwitter();

    }
    
    
    
        
    function pollTwitter(){
    
        /* Create a TwitterOauth object with consumer/user tokens. */
	/* make sure they're defined in config.php */
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);
        
        $connection->format = 'json';
        
        if ($this->screen_name){
            $content = $connection->get('statuses/user_timeline', array('screen_name' => $this->screen_name));
        } else {
            die(MISSING_SCREEN_NAME);
        }
        
        $twitterRSSFeed = new TwitterRSSWriter($content);
        $twitterRSSFeed->populateHeadData();
        $twitterRSSFeed->createHead();
        $twitterRSSFeed->writeItems();
        
       // print_r($twitterRSSFeed);
        
        $rssContent = $twitterRSSFeed->returnXML();
        
        $fileHandle = fopen($this->feedfileName, "w");
        fwrite($fileHandle, $rssContent);
        fclose($fileHandle);
        
        header("Content-type: text/xml");
        echo $rssContent;
        die();
        
    }
}

?>
<?php	    

    class rssWriter {
    	    
        private $doc;
        private $channel;
        
        function __construct($collection){
            $this->collection = $collection;
        }
        
        function populateHeadData($headData){

            $this->feedTitle = (isset($headData->title)) ? $headData->title : "RSS Feed";
            $this->feedLink = (isset($headData->url)) ? $headData->url : $_SERVER["REQUEST_URI"];
            $this->feedDescription = (isset($headData->description)) ? $headData->description : "RSS Feed";
        
        }
        
        function createHead(){
        
    	    $this->doc = new DOMDocument('1.0');
    		
    		$root = $this->doc->createElement("rss");
    		$root->setAttribute("version", "2.0"); 
    		$this->doc->appendChild($root);
    	
    		$this->channel = $this->doc->createElement("channel");
    		$root->appendChild($this->channel);
    		
    		$title = $this->doc->createElement("title", $this->feedTitle);
    		$this->channel->appendChild($title);
    	
    		$link = $this->doc->createElement("link", $this->feedLink);
    		$this->channel->appendChild($link);
    	
    		$description = $this->doc->createElement("description", $this->feedDescription);
    		$this->channel->appendChild($description);
    	
    		$language = $this->doc->createElement("language", "en-us");
    		$this->channel->appendChild($language);
    	
    	}
    	
    	function writeItems(){
    	
    		if (sizeof($this->collection) > 0){
    			
                foreach($this->collection as $item){

                    //create item node
    				$newItem = $this->channel->appendChild($this->doc->createElement("item", ""));
                    // append title
    				$newItem->appendChild($this->doc->createElement("title", $this->_prepareTitle($item)));
    				// append description
    				$newItem->appendChild($this->doc->createElement("description", $this->_prepareDescription($item)));
                    // append PubDate
    				$newItem->appendChild($this->doc->createElement("pubDate", $this->_preparePubDate($item)));
                    // append guid
    				$newItem->appendChild($this->doc->createElement("guid", $this->_prepareLinkURL($item)));
                    // append link
    				$newItem->appendChild($this->doc->createElement("link", $this->_prepareLinkURL($item)));
                    
    				$newItem = '';
    
    			}
    		
    		}	
        }
        
        function returnXML(){
            return $this->doc->saveXML();
        }
        
        function _prepareTitle($obj){
            if (isset($obj->title)) { return $obj->title; }
        }
        
        function _prepareDescription($obj){
            if (isset($obj->description)) { return $obj->description; }
        }
        
        function _preparePubDate($obj){
            if (isset($obj->pubdate)) { return $obj->pubdate; }
        }
        
        function _prepareLinkURL($obj){
            if (isset($obj->url)) { return $obj->url; }
        }
        
    }
    
    
    class twitterRSSWriter extends rssWriter {

        function populateHeadData(){

            $this->feedTitle = "Twitter / " . $this->collection[0]->user->screen_name;
            $this->feedLink = "https://twitter.com/" . $this->collection[0]->user->screen_name;
            $this->feedDescription = "Twitter updates from " . $this->collection[0]->user->name . " / " . $this->collection[0]->user->screen_name . ".";
        
        }
        
        function _prepareTitle($obj){
            return $this->_prepareDescription($obj);
        }
        
        function _prepareDescription($obj){
            if (isset($obj->text)) { return $obj->user->screen_name . ": " . $obj->text; }
        }
        
        function _preparePubDate($obj){
            if (isset($obj->created_at)) { 
               return strftime("%a, %d %b %Y %T", strtotime($obj->created_at)) . " +0000";
            }
        }
        
        function _prepareLinkURL($obj){
            if (isset($obj->id)) { return "https://twitter.com/" . $obj->user->screen_name . "/status/" . $obj->id; }
        }
        
    }
?>
<?php

class RssReader {
	var $posts = array();
	
	function getFeedArray($pUrl) {
		$xml_string = $this->resolveFile($pUrl);
		
        if (!($x = simplexml_load_string($xml_string)))
            return;

		//return $x;

        foreach ($x->channel->item as $item)
        {
            $post['date']      = (string) $item->pubDate;
            $post['timestamp'] = strtotime($item->pubDate);
            $post['link']      = (string) $item->link;
            $post['title'] 	   = (string) $item->title;
            $post['text'] 	   = (string) $item->description;
			
            $post['summary'] = $this->summarizeText($post['text']);

			if(!empty($post['title']) && !empty(json_encode($post['title'])) && !empty($post['summary']) && !empty(json_encode($post['summary'])) ) {
            	$this->posts[] = $post;
			}
        }
		
		return $this->posts;
	}
	
	private function resolveFile($file_or_url) {
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		
		$xml = file_get_contents($file_or_url, false, $context);

        return $xml;
    }

    private function summarizeText($summary) {
        $summary = strip_tags($summary);

        // Truncate summary line to 100 characters
        $max_len = 100;
        if (strlen($summary) > $max_len)
            $summary = substr($summary, 0, $max_len) . '...';

        return $summary;
    }
}

?>
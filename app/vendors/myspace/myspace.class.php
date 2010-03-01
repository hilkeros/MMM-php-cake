<?php

/*   MySpace Basic Class   */



class myspace_profile

{

        var $profile;

        var $data;



        // Build up page data

        function myspace_profile($input)

        {

                $this->profile = $input;

                $dataFile = fopen($this->profile,"r");



                if ($dataFile)

                {

                        while (!feof($dataFile))

                        {

                                $this->data .= fgets($dataFile, 4096);

                        }

                        fclose($dataFile);

                }

                else

                {

                        return false;

                }

        }


	

        // Strip function

        function strip($str, $start, $end)

        {
		$start =  strtolower($start);	
	
                if(empty($str))

                {
                        return;
                } // if(empty($str))
                $str_low = strtolower($str);
                $pos_start = strpos($str_low, $start);
	        $pos_end = strpos($str_low, $end, ($pos_start + strlen($start)));

                if ( ($pos_start !== false) && ($pos_end !== false) )

                {

                        $pos1 = $pos_start + strlen($start);

                        $pos2 = $pos_end - $pos1;

                        return substr($str, $pos1, $pos2);

                }  //                 if ( ($pos_start !== false) && ($pos_end !== false) )

        } //         function strip($str, $start, $end)

 	function striplayer($str, $start, $end)
        {
                if(empty($str))

                {

                        return;

                } //          if(empty($str))
		$str_low = $str;
		$pos_start = strpos($str_low, $start);
                $pos_end = strpos($str_low, $end, ($pos_start + strlen($start)));
		
            	if ( ($pos_start !== false) && ($pos_end !== false) )
                {
                        $pos1 = $pos_start + strlen($start);
                        $pos2 = $pos_end - $pos1;
			return substr($str, $pos1, $pos2);

                } //                 if ( ($pos_start !== false) && ($pos_end !== false) )
	} // function striplayer($str, $start, $end)


	// Get Friends
	function get_friends()
	{
		return trim($this->strip($this->data,"<span class=\"redbtext\" property=\"myspace:friendCount\">","</span>"));
	} // function get_friends()

	
	// Get Flash Player
	function get_player()
	{
		
		//$str="new SWFObject(\"";
		$str= "<div id=\"profile_mp3Player\"";
		$end = "</div>";
		

		$url= trim($this->striplayer($this->data, $str,$end));
		
		
		$id['plid']= trim($this->strip($url,'plid','&'));
		$id['artid'] = trim($this->strip($url,'artid','&'));
		$id['profid'] = trim($this->strip($url,'profid','&'));
		//print_r($id);
		//exit;
		return $id;
	} // 	function get_player()


	// Get Profile View
	function get_profile_view()
        {
		$str = "<span property=\"myspace:headline\">";
		return trim($this->strip($this->data, $str, "</td>"));
        } //	function get_profile_view()

	function get_name()
        {
		$str = "<span class=\"nametext\">";
		return trim($this->strip($this->data, $str, "</span>"));
        } //	function get_profile_view()

	// Get Number of comments
	function get_no_comments()
	{
		return trim($this->strip($this->data, "/<span class=\"redtext\">","</span>"));
	}
        // Get comments return (array)

        function get_comments()
        {
                $tmp = explode("<table width=\"100%\" border=\"1\" cellspacing=\"0\" cellpadding=\"3\" bordercolor=\"FFffff\">", $this->data);
                $tmp2 = explode("</table> </td> </tr>", $tmp[1]);
                $tmp3 = explode("<tr>", $tmp2[0]);
		$comments_xml = NULL;

                for($i=1;$i<count($tmp3);$i++)
                {
                       $comments_xml .= "<u:break>\r\n";

                        // User Profile Link

                      $comments_xml .= "<u:link>".trim($this->strip($tmp3[$i], "<a href=\"", "\">"))."</u:link>\r\n";



                        // User Profile Name
			$start= trim($this->strip($tmp3[$i], "<a href=\"", "\">"))."\">";
			$comments_xml .= "<u:name>".trim($this->strip($tmp3[$i],$start,"</a"))."</u:name>\r\n";



                        // User Profile Link
                    	$comments_xml .= "<c:date>".trim($this->strip($tmp3[$i], "<span class=\"blacktext10\">", "</span>"))."</c:date>\r\n";


                        // Profile Comment
	                $comments_xml .= "<c:comment>".trim($this->strip($tmp3[$i], "</span>", "</td>"))."</c:comment>\r\n";

                        $comments_xml .= "</u:break>\r\n";

                }  // for($i=1;$i<count($tmp3);$i++)



                return $comments_xml;

        } //         function get_comments()

}
?>

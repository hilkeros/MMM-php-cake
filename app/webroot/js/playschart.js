
	
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
        if(str.search('plays')!= -1)
        {
            document.location.href = base+"/analytics/cmslist/";
        }
      
        
	$(document).ready(function(){
            var id  = $("#analyticDate").val();
                                          
            $("#link").attr("href", baseurl+"&date="+id);
	    $("#ms").attr("href", baseurl+"&date="+id+"&id=msp");
	    $("#yt").attr("href", baseurl+"&date="+id+"&id=ytp");
	    $("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsp");
	 
	    
            
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id);
		$("#ms").attr("href", baseurl+"&date="+id+"&id=msp");
		$("#yt").attr("href", baseurl+"&date="+id+"&id=ytp");
		$("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsp");
		
                
            })
        });

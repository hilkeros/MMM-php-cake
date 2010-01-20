
	
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
        if(str.search('views')!= -1)
        {
            document.location.href = base+"/analytics/cmslist/";
        }
      
        
	$(document).ready(function(){
            var id  = $("#analyticDate").val();
                                          
            $("#link").attr("href", baseurl+"&date="+id);
	    $("#ms").attr("href", baseurl+"&date="+id+"&id=msv");
	    $("#yt").attr("href", baseurl+"&date="+id+"&id=ytv");
	    $("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsv");
	 
	    
            
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id);
		$("#ms").attr("href", baseurl+"&date="+id+"&id=msv");
		$("#yt").attr("href", baseurl+"&date="+id+"&id=ytv");
		$("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsv");
		
                
            })
        });

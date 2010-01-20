
	
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
        if(str.search('hits')!= -1)
        {
            document.location.href = base+"/analytics/cmslist/";
        }
      
        
	$(document).ready(function(){
            var id  = $("#analyticDate").val();
                                          
            $("#link").attr("href", baseurl+"&date="+id);
	    $("#ms").attr("href", baseurl+"&date="+id+"&id=msh");
	    $("#yt").attr("href", baseurl+"&date="+id+"&id=yth");
	    $("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsh");
	    $("#fbspages").attr("href", baseurl+"&date="+id+"&id=fbspages");
	    $("#fbsgroups").attr("href", baseurl+"&date="+id+"&id=fbsgroups");
	    
            
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id);
		$("#ms").attr("href", baseurl+"&date="+id+"&id=msh");
		$("#yt").attr("href", baseurl+"&date="+id+"&id=yth");
		$("#lfms").attr("href", baseurl+"&date="+id+"&id=lfmsh");
		$("#fbspages").attr("href", baseurl+"&date="+id+"&id=fbspages");
		$("#fbsgroups").attr("href", baseurl+"&date="+id+"&id=fbsgroups");
                
            })
        });

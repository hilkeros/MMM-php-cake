
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
	if(str.search('chart')!= -1)
        {
            document.location.href = base+"/dashboard/index/";
        }
      
      
	$(document).ready(function(){
           
	    var id  = $("#analyticFrdate").val();
            var opt = $("#analyticFropt").val();
            $("#frlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=follower");
            
            var fid  = $("#analyticFgdate").val();
            var fopt = $("#analyticFgopt").val();
            $("#fglink").attr("href", baseurl+"&date="+fid+"&opt="+fopt+"&type=following");
            
            var cid  = $("#analyticTdate").val();
            var copt = $("#analyticTopt").val();
            $("#tlink").attr("href", baseurl+"&date="+cid+"&opt="+copt+"&type=tweets");
            
            
            
            var pid  = $("#analyticFdate").val();
            var popt = $("#analyticFopt").val();
            $("#flink").attr("href", baseurl+"&date="+pid+"&opt="+popt+"&type=favorites");
     	
	});
        
        $(function(){
        
                            
            $('select#analyticFrdate').change(function()
            {
                var id  = $("#analyticFrdate").val();
                var opt = $("#analyticFropt").val();
                $("#frlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=follower");
                
            })
            
            $('select#analyticFropt').change(function()
            {
                var id  = $("#analyticFrdate").val();
                var opt = $("#analyticFropt").val();
                $("#frlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=follower");
                
            })
            
            $('select#analyticFgdate').change(function()
            {
                var id  = $("#analyticFgdate").val();
                var opt = $("#analyticFgopt").val();
                $("#fglink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=following");
            })
            
            $('select#analyticFgopt').change(function()
            {
                var id  = $("#analyticFgdate").val();
                var opt = $("#analyticFgopt").val();
                $("#fglink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=following");
            })
            
            $('select#analyticTdate').change(function()
            {
                var id  = $("#analyticTdate").val();
                var opt = $("#analyticTopt").val();
                $("#tlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=tweets");
            })
            
            $('select#analyticTopt').change(function()
            {
                var id  = $("#analyticTdate").val();
                var opt = $("#analyticTopt").val();
                $("#tlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=tweets");
            })
           
            $('select#analyticFdate').change(function()
            {
                var id  = $("#analyticFdate").val();
                var opt = $("#analyticFopt").val();
                $("#flink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=favorites");        
            })
            
            $('select#analyticFopt').change(function()
            {
            
                var id  = $("#analyticFdate").val();
                var opt = $("#analyticFopt").val();
                $("#flink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=favorites");    
                
            })
	    
        });

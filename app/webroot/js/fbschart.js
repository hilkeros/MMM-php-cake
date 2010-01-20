
	
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
        if(str.search('chart')!= -1)
        {
            document.location.href = base+"/dashboard/index/";
        }
      
        	
	$(document).ready(function(){
            var id  = $("#analyticDate").val();
            var opt = $("#analyticOpt").val();
           
                               
            $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=page");
            
            var fid  = $("#analyticGdate").val();
            var fopt = $("#analyticGopt").val();
            $("#glink").attr("href", baseurl+"&date="+fid+"&opt="+fopt+"&type=group");
            
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=page");
                
            })
            
            $('select#analyticOpt').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=page");
            })
            
            $('select#analyticGdate').change(function()
            {
                var id  = $("#analyticGdate").val();
                var opt = $("#analyticGopt").val();
                $("#glink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=group");
            })
            
            $('select#analyticGopt').change(function()
            {
                var id  = $("#analyticGdate").val();
                var opt = $("#analyticGopt").val();
                $("#glink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=group");
            })

        });

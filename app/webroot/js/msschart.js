
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
	if(str.search('chart')!= -1)
        {
            document.location.href = base+"/dashboard/index/";
        }
      
        var url = new Array(); 		// Array to store plays 
	var curl = new Array();		// Array to store downloads
	
	$(document).ready(function(){
            var id  = $("#analyticDate").val();
            var opt = $("#analyticOpt").val();
           
                               
            $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=views");
            
            var fid  = $("#analyticFdate").val();
            var fopt = $("#analyticFopt").val();
            $("#flink").attr("href", baseurl+"&date="+fid+"&opt="+fopt+"&type=friends");
            
            var cid  = $("#analyticCdate").val();
            var copt = $("#analyticCopt").val();
            $("#clink").attr("href", baseurl+"&date="+cid+"&opt="+copt+"&type=comments");
            
            
            
            var pid  = $("#analyticPdate").val();
            var popt = $("#analyticPopt").val();
            $("#plink").attr("href", baseurl+"&date="+pid+"&opt="+popt+"&type=plays");
            
            if(url.length==0){
                    $('#plays a').each(function(){
                    
                         url[$(this).attr('id')] = $(this).attr('href');
                    });
                }
                
                $('#plays a').each(function(){
	
		    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', url[$(this).attr('id')]+"&title="+title+"&date="+pid+'&opt='+popt+'&type=plays&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&title="+$(this).attr('text')+"&date="+pid+'&opt='+popt+'&type=plays&ids='+$(this).attr('id'));			
		    }
	        });
		
	    var did  = $("#analyticDdate").val();
            var dopt = $("#analyticDopt").val();
            $("#dlink").attr("href", baseurl+"&date="+cid+"&opt="+copt+"&type=downloads");		
	
	    // if comments curl empty then set default url for all attributes for comments	
	    if(curl.length==0)
	    {
		    $('#downloads a').each(function(){
		    
			 curl[$(this).attr('id')] = $(this).attr('href');
		    });
	    }
		
		$('#downloads a').each(function(){
		    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', curl[$(this).attr('id')]+"&title="+title+"&date="+did+'&opt='+dopt+'&type=downloads&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+did+'&opt='+dopt+'&type=downloads&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
		});
		
		
		
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=views");
                
            })
            
            $('select#analyticOpt').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=views");
            })
            
            $('select#analyticFdate').change(function()
            {
                var id  = $("#analyticFdate").val();
                var opt = $("#analyticFopt").val();
                $("#flink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=friends");
            })
            
            $('select#analyticFopt').change(function()
            {
                var id  = $("#analyticFdate").val();
                var opt = $("#analyticFopt").val();
                $("#flink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=friends");
            })
            
            $('select#analyticCdate').change(function()
            {
                var id  = $("#analyticCdate").val();
                var opt = $("#analyticCopt").val();
                $("#clink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=comments");
            })
            
            $('select#analyticCopt').change(function()
            {
                var id  = $("#analyticCdate").val();
                var opt = $("#analyticCopt").val();
                $("#clink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=comments");
            })
           
            $('select#analyticPdate').change(function()
            {
                var id  = $("#analyticPdate").val();
                var opt = $("#analyticPopt").val();
                $("#plink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=plays");        
                
                $('#plays a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', url[$(this).attr('id')]+"&title="+title+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&title="+$(this).attr('text')+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id'));			
		    }
                });

            });
            
            $('select#analyticPopt').change(function()
            {
            
                var id  = $("#analyticPdate").val();
                var opt = $("#analyticPopt").val();
                $("#plink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=plays");
                
                $('#plays a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', url[$(this).attr('id')]+"&title="+title+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&title="+$(this).attr('text')+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id'));			
		    }
                });
            
            })
	    
	     $('select#analyticDdate').change(function()
            {
                var id  = $("#analyticDdate").val();
                var opt = $("#analyticDopt").val();
                $("#dlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=downloads");        
                
                $('#downloads a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', curl[$(this).attr('id')]+"&title="+title+"&date="+id+'&opt='+opt+'&type=downloads&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=downloads&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });

            });
            
            $('select#analyticDopt').change(function()
            {
            
                var id  = $("#analyticDdate").val();
                var opt = $("#analyticDopt").val();
                $("#dlink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=downloads");
                
                $('#downloads a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
		        $(this).attr('href', curl[$(this).attr('id')]+"&title="+title+"&date="+id+'&opt='+opt+'&type=downloads&ids='+$(this).attr('id'));			
		    }
		    else
		    {
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=downloads&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });
            
            })
	    
	    
        });

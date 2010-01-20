        
        
	// redirect to dashboard home if call without lightbox
        var str = document.location.href;
        if(str.search('chart')!= -1)
        {
            document.location.href = base+"/dashboard/index/";
        }
      
      
      
        var url = new Array(); 		// Array to store plays 
		
	// Ajax funcation call when document ready
	
	$(document).ready(function(){
	    
            var id  = $("#analyticDate").val();
            var opt = $("#analyticOpt").val();
            $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=listener");
	    
	     var id  = $("#analyticAdate").val();
            var opt = $("#analyticAopt").val();
            $("#alink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=album");
            
            var pid  = $("#analyticPdate").val();
            var popt = $("#analyticPopt").val();
            $("#plink").attr("href", baseurl+"&date="+pid+"&opt="+popt+"&type=plays");
            
	    // if plays url empty then set default url for all attributes for plays
	    
	    if(url.length==0)
	    {
                    $('#plays a').each(function(){
                    
                         url[$(this).attr('id')] = $(this).attr('href');
                    });
            }
                
	    $('#plays a').each(function(){
		 var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+pid+'&opt='+popt+'&type=plays&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+pid+'&opt='+popt+'&type=plays&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
		
	    });

	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=listener");
                
            })
            
            $('select#analyticOpt').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=listener");
            })
	    
	     $('select#analyticAdate').change(function()
            {
                var id  = $("#analyticAdate").val();
                var opt = $("#analyticAopt").val();
                $("#alink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=album");
                
            })
            
            $('select#analyticAopt').change(function()
            {
                var id  = $("#analyticAdate").val();
                var opt = $("#analyticAopt").val();
                $("#alink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=album");
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
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
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
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=plays&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });
            
            })
	    
        });  

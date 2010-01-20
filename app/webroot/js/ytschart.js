        
        
	// redirect to dashboard home if call without lightbox
        var str = document.location.href;
        if(str.search('chart')!= -1)
        {
            document.location.href = base+"/dashboard/index/";
        }
      
      
      
        var url = new Array(); 		// Array to store plays 
	var curl = new Array();		// Array to store comments
	
	// Ajax funcation call when document ready
	
	$(document).ready(function(){
	    
            var id  = $("#analyticDate").val();
            var opt = $("#analyticOpt").val();
            $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=channel");
            
            var fid  = $("#analyticFdate").val();
            var fopt = $("#analyticFopt").val();
            $("#flink").attr("href", baseurl+"&date="+fid+"&opt="+fopt+"&type=friends");
            
            var sid  = $("#analyticSdate").val();
            var sopt = $("#analyticSopt").val();
            $("#slink").attr("href", baseurl+"&date="+cid+"&opt="+copt+"&type=subscribers");
            
            
            
            var pid  = $("#analyticPdate").val();
            var popt = $("#analyticPopt").val();
            $("#plink").attr("href", baseurl+"&date="+pid+"&opt="+popt+"&type=views");
            
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
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+pid+'&opt='+popt+'&type=views&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+pid+'&opt='+popt+'&type=views&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                    
                });

	    var cid  = $("#analyticCdate").val();
            var copt = $("#analyticCopt").val();
            $("#clink").attr("href", baseurl+"&date="+cid+"&opt="+copt+"&type=total_comments");		
	
	    // if comments curl empty then set default url for all attributes for comments	
	    if(curl.length==0)
	    {
		    $('#comments a').each(function(){
		    
			 curl[$(this).attr('id')] = $(this).attr('href');
		    });
	    }
		
		$('#comments a').each(function(){
		     var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+cid+'&opt='+copt+'&type=total_comments&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			  $(this).attr('href', curl[$(this).attr('id')]+"&date="+cid+'&opt='+copt+'&type=total_comments&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
		    
		});
	});
        
        $(function(){
        
                            
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=channel");
                
            })
            
            $('select#analyticOpt').change(function()
            {
                var id  = $("#analyticDate").val();
                var opt = $("#analyticOpt").val();
                $("#link").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=channel");
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
            
            $('select#analyticSdate').change(function()
            {
                var id  = $("#analyticSdate").val();
                var opt = $("#analyticSopt").val();
                $("#slink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=subscribers");
            })
            
            $('select#analyticSopt').change(function()
            {
                var id  = $("#analyticSdate").val();
                var opt = $("#analyticSopt").val();
                $("#slink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=subscribers");
            })
           
            $('select#analyticPdate').change(function()
            {
                var id  = $("#analyticPdate").val();
                var opt = $("#analyticPopt").val();
                $("#plink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=views");        
                
                $('#plays a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=views&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=views&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });

            });
            
            $('select#analyticPopt').change(function()
            {
            
                var id  = $("#analyticPdate").val();
                var opt = $("#analyticPopt").val();
                $("#plink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=views");
                
                $('#plays a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=views&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			$(this).attr('href', url[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=views&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });
            
            })
	    
	    $('select#analyticCdate').change(function()
            {
                var id  = $("#analyticCdate").val();
                var opt = $("#analyticCopt").val();
                $("#clink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=comments");        
                
                $('#comments a').each(function(){
                    var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=comments&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			  $(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=comments&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });

            });
            
            $('select#analyticCopt').change(function()
            {
            
                var id  = $("#analyticCdate").val();
                var opt = $("#analyticCopt").val();
                $("#clink").attr("href", baseurl+"&date="+id+"&opt="+opt+"&type=comments");
                
                $('#comments a').each(function(){
                     var sBrowser = navigator.userAgent;
		    if (sBrowser.toLowerCase().indexOf('msie') > -1)
		    {
			
			var title =encodeURI( $(this).attr('innerHtml') );
			
			$(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=comments&ids='+$(this).attr('id')+'&title='+title);
		    }
		    else
		    {
			  $(this).attr('href', curl[$(this).attr('id')]+"&date="+id+'&opt='+opt+'&type=comments&ids='+$(this).attr('id')+'&title='+$(this).attr('text'));
		    }
                });
            
            })
        });  

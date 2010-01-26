		
	// redirect to dashboard home if call without lightbox
	var str = document.location.href;
	if(str.search('chart') != -1)
        {
	    document.location.href = base+"/dashboard/index/";
        }
      
        	
	$(document).ready(function(){
            // for fans
	    var id  = $("#analyticDate").val();
	    $("#fall").attr("href", baseurl+"&date="+id+"&type=fans&id=fall");
	    $("#fnone").attr("href", baseurl+"&date="+id+"&type=fans&id=fnone");
            $("#link").attr("href", baseurl+"&date="+id+"&type=fans");
	    $("#msh").attr("href", baseurl+"&date="+id+"&id=msh&type=fans");
	    $("#yth").attr("href", baseurl+"&date="+id+"&id=yth&type=fans");
	    $("#fbspages").attr("href", baseurl+"&date="+id+"&id=fbspages&type=fans");
	    $("#fbsgroups").attr("href", baseurl+"&date="+id+"&id=fbsgroups&type=fans");
	    
            // for hits
            var hid  = $("#analyticHdate").val();
	    $("#hall").attr("href", baseurl+"&date="+hid+"&type=hits&id=hall");
	    $("#hnone").attr("href", baseurl+"&date="+hid+"&type=hits&id=hnone");
	    $("#hlink").attr("href", baseurl+"&date="+hid+"&type=hits");
	    $("#msv").attr("href", baseurl+"&date="+hid+"&id=msv&type=hits");
	    $("#ytv").attr("href", baseurl+"&date="+hid+"&id=ytv&type=hits");
            
	    // for tracks
            var tid  = $("#analyticTdate").val();
	    var mss  = encodeURI($("#analyticMss").val());
	    var yt  = encodeURI($("#analyticYt").val());
	    var lfmt  = encodeURI($("#analyticLfmt").val());
	    $("#tall").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tall");
	    $("#tnone").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tnone");
            $("#tlink").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
            $("#msp").attr("href", baseurl+"&date="+tid+"&id=msp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
	    $("#ytp").attr("href", baseurl+"&date="+tid+"&id=ytp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
	    $("#lfmsp").attr("href", baseurl+"&date="+tid+"&id=lfmsp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
	    
	    // for comments
	    var cid  = $("#analyticCdate").val();
	    var yt  = encodeURI($("#analyticYt").val());
	    $("#call").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=call");
	    $("#cnone").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=cnone");
	    $("#clink").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments");
	    $("#msc").attr("href", baseurl+"&date="+cid+"&id=msc&yt="+yt+"&type=comments");
	    $("#ytc").attr("href", baseurl+"&date="+cid+"&id=ytc&yt="+yt+"&type=comments");
	    
	});
        
        $(function(){
        
            // for Fans              
            $('select#analyticDate').change(function()
            {
                var id  = $("#analyticDate").val();
		$("#fall").attr("href", baseurl+"&date="+id+"&type=fans&id=fall");
		$("#fnone").attr("href", baseurl+"&date="+id+"&type=fans&id=fnone");
                $("#link").attr("href", baseurl+"&date="+id+"&type=fans");
	        $("#msh").attr("href", baseurl+"&date="+id+"&id=msh&type=fans");
		$("#yth").attr("href", baseurl+"&date="+id+"&id=yth&type=fans");
		$("#lfmsh").attr("href", baseurl+"&date="+id+"&id=lfmsh&type=fans");
		$("#fbspages").attr("href", baseurl+"&date="+id+"&id=fbspages&type=fans");
		$("#fbsgroups").attr("href", baseurl+"&date="+id+"&id=fbsgroups&type=fans");
	       
                
            })
            // end fans
            
	    // For hits           
            $('select#analyticHdate').change(function()
            {
                var id  = $("#analyticHdate").val();
		$("#hall").attr("href", baseurl+"&date="+id+"&type=hits&id=hall");
		$("#hnone").attr("href", baseurl+"&date="+id+"&type=hits&id=hnone");
		$("#hall").attr("href", baseurl+"&date="+id+"&type=hits&id=hall");
		$("#hnone").attr("href", baseurl+"&date="+id+"&type=hits&id=hnone");
		$("#hlink").attr("href", baseurl+"&date="+id+"&type=hits");
		$("#msv").attr("href", baseurl+"&date="+id+"&id=msv&type=hits");
		$("#ytv").attr("href", baseurl+"&date="+id+"&id=ytv&type=hits");
		$("#lfmsv").attr("href", baseurl+"&date="+id+"&id=lfmsv&type=hits");
            })
            
	   
	    // end hits
	    
	    // for tracks
                   
            $('select#analyticTdate').change(function()
            {
                var id  = $("#analyticTdate").val();
		var mss  = encodeURI($("#analyticMss").val());
		var yt  = encodeURI($("#analyticYt").val());
		var lfmt  = encodeURI($("#analyticLfmt").val());
		$("#tall").attr("href", baseurl+"&date="+id+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tall");
		$("#tnone").attr("href", baseurl+"&date="+id+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tnone");
		$("#tlink").attr("href", baseurl+"&date="+id+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#msp").attr("href", baseurl+"&date="+id+"&id=msp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#ytp").attr("href", baseurl+"&date="+id+"&id=ytp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#lfmsp").attr("href", baseurl+"&date="+id+"&id=lfmsp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
            })
	    
	    $('select#analyticMss').change(function()
            {
                var tid  = $("#analyticTdate").val();
		var mss  = encodeURI($("#analyticMss").val());
		var yt  = encodeURI($("#analyticYt").val());
		var lfmt  = encodeURI($("#analyticLfmt").val());
		$("#tall").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tall");
		$("#tnone").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tnone");
		$("#tlink").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#msp").attr("href", baseurl+"&date="+tid+"&id=msp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#ytp").attr("href", baseurl+"&date="+tid+"&id=ytp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#lfmsp").attr("href", baseurl+"&date="+tid+"&id=lfmsp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
            })
	    
	    $('select#analyticYt').change(function()
            {
                var tid  = $("#analyticTdate").val();
		var mss  = encodeURI($("#analyticMss").val());
		var yt  = encodeURI($("#analyticYt").val());
		var lfmt  = encodeURI($("#analyticLfmt").val());
		$("#tall").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tall");
		$("#tnone").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tnone");
		$("#tlink").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#msp").attr("href", baseurl+"&date="+tid+"&id=msp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#ytp").attr("href", baseurl+"&date="+tid+"&id=ytp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#lfmsp").attr("href", baseurl+"&date="+tid+"&id=lfmsp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
            })
	    
	    $('select#analyticLfmt').change(function()
            {
                var tid  = $("#analyticTdate").val();
		var mss  = encodeURI($("#analyticMss").val());
		var yt  = encodeURI($("#analyticYt").val());
		var lfmt  = encodeURI($("#analyticLfmt").val());
		$("#tall").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tall");
		$("#tnone").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks&id=tnone");
		$("#tlink").attr("href", baseurl+"&date="+tid+"&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#msp").attr("href", baseurl+"&date="+tid+"&id=msp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
		$("#ytp").attr("href", baseurl+"&date="+tid+"&id=ytp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");	
		$("#lfmsp").attr("href", baseurl+"&date="+tid+"&id=lfmsp&mss="+mss+"&yt="+yt+"&lfmt="+lfmt+"&type=tracks");
            })
	    // end tracks
	    
	    
            // for comments
                      
            $('select#analyticCdate').change(function()
            {
               var cid  = $("#analyticCdate").val();
		var yt  = encodeURI($("#analyticYtc").val());
		$("#call").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=call");
		$("#cnone").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=cnone");
		$("#clink").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments");
		$("#msc").attr("href", baseurl+"&date="+cid+"&id=msc&yt="+yt+"&type=comments");
		$("#ytc").attr("href", baseurl+"&date="+cid+"&id=ytc&yt="+yt+"&type=comments");
            })
	    
	    $('select#analyticYtc').change(function()
            {
               var cid  = $("#analyticCdate").val();
		var yt  = encodeURI($("#analyticYtc").val());
		$("#call").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=call");
		$("#cnone").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments&id=cnone");
		$("#clink").attr("href", baseurl+"&date="+cid+"&yt="+yt+"&type=comments");
		$("#msc").attr("href", baseurl+"&date="+cid+"&id=msc&yt="+yt+"&type=comments");
		$("#ytc").attr("href", baseurl+"&date="+cid+"&id=ytc&yt="+yt+"&type=comments");
            })
            // end comments  
            
        });

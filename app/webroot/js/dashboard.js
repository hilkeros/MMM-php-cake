
   var w = (screen.width/2);
   var h = (screen.height/2);
    
   var left = (screen.width/2)-(w/2);
   var top = (screen.height/2)-(h/2);

    /* name : toggle
     * description : set Collapse and Expand twitter / netlog / hyves / bebo area
     *
     */
    function toggle(showHideDiv, switchImgTag , tagimg)
    {
	    var ele = document.getElementById(showHideDiv);
	    var imageEle = document.getElementById(switchImgTag);
	    var tagimage = document.getElementById(tagimg);
	  
	    
	    if(ele.style.display == "none")
	    {
		    tagimage.style.display = "none";
		    ele.style.display = "block";
		    document.getElementById("imageDivLinkimg").innerHTML = "Collapse";
		    
	    
	    }
	    else
	    {
		    
		    tagimage.style.display = "block";
		    ele.style.display = "none";
		    document.getElementById("imageDivLinkimg").innerHTML = "Expand";
		    
		    
	    }
	    
    }
	
    /* name : twttoggle
     * description : Expand and Collapse twitter tweets , mentions and Dms area
     *
     */	
    function twttoggle(id , block)
    {
	    var id = document.getElementById(id);
	    var block = document.getElementById(block);
	    
	    id.style.display	=	"none";
	    block.style.display	= 	"block";
    }
    
    
    
    //Edit the counter/limiter value as your wish
    var count = "140";   //Example: var count = "175";
    /*
     * name :  limiter
     * description : count character in update status area . Maximum of 140
     */
    function limiter(){
	
    var tex = document.twtform.DashboardStatus.value;
    var len = tex.length;
    if(len > count){
	    tex = tex.substring(0,count);
	    document.twtform.DashboardStatus.value =tex;
	    return false;
    }
    document.twtform.limit.value = count-len;
    }
    

    /* name : fbalert
     * description : Feedback alert message.
     *
     */	    
    function fbalert()
    {
	
	os = document.getElementById('UsersOs').value;
	data = document.getElementById('UsersFeedback').value;

	if(os=="")
	{
	    alert("Operating system is required.");
	    document.getElementById('UsersOs').focus();
	    return false;
	}
	
	if(data=="")
	{
	    alert("Please enter feedback text.");
	    document.getElementById('UsersFeedback').focus();
	    return false;
	    
	}
	
	alert("Thank you very much for your feedback. We will read it with the greatest care.");
	return true;
    }
	
    
    /* 
     * jquery function execute when page ready / load
     */	    
  $(document).ready(function(){
	       
	       
    var width= Math.round(screen.width/1.75);
    var height = Math.round(screen.height/1.75);
    $(".container").css("height","100%");
        
    $("#myspace").attr("href",$("#myspace").attr("href")+'&width='+width+'&height='+height);
    $("#yts").attr("href",$("#yts").attr("href")+'&width='+width+'&height='+height);
    $("#lfms").attr("href",$("#lfms").attr("href")+'&width='+width+'&height='+height);
    $("#fbs").attr("href",$("#fbs").attr("href")+'&width='+width+'&height='+height);
    $("#twt").attr("href",$("#twt").attr("href")+'&width='+width+'&height='+height);
    $("#feedback-form").attr("href",$("#feedback-form").attr("href")+'?width='+width+'&height='+height);
    
    // update twitter database
    $.getJSON(base+"/twitter/updateTwitter/",{ bandid: $("input[name='data[Twitter][bandid]']").val() , ajax: 'true'} , function(j){
		  	  
		})
    
    // get & set direct messages data
    $.getJSON(base+"/twitter/getDms/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val() , count:10 , ajax: 'true'}, function(j){
		  var message = '';
		  var name = '';
		  var vdate = '';
		  var flag = '';
		  var DmsData = '';
		  var DmsCount = 0 ;
		  
		  for (var i = 0; i < j.length; i++) {
		 
			   message 	= j[i].optionText; 
			   name 	= j[i].optionName;
			   vdate 	= j[i].optionDate;
			   flag 	= j[i].optionFlag;
			   DmsCount	= j[i].optionCount;
			   
			   DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=dm-text > "+message+"</div> <div class=dms-desc><a href=http://twitter.com/"+name+" target=_blank>"+name+"</a></div><div class=dms-created_at>"+vdate+"</div></div><div class=twt-line></div>";
			   
   
		  }
		  
		  
		  $("div#twt_dm_message").html(DmsData);
		  $("span#dms_number").html(DmsCount);
		  
		  if(flag==0)
		  {
		    $("#dms-more").css("display","none");
		    $("#dm-text").css("text-align","center");
		  
		   //  $("#dms-messages").css("overflow-x","hidden");
		  }
		  
		})
    
    // get & set mentions data
    $.getJSON(base+"/twitter/getMentions/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(),count:10 , ajax: 'true'}, function(j){
		  var message = '';
		  var name = '';
		  var vdate = '';
		  var flag = '';
		  var DmsData = '';
		  var MentionsCount = 0; 
		  for (var i = 0; i < j.length; i++) {
		 
			   message 	= j[i].optionText; 
			   name 	= j[i].optionName;
			   vdate 	= j[i].optionDate;
			   flag 	= j[i].optionFlag;
			   MentionsCount = j[i].optionCount;
			   
			   DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=mentions-text > "+message+"</div> <div class=dms-desc><a href=http://twitter.com/"+name+" target=_blank>"+name+"</a></div><div class=dms-created_at>"+vdate+"</div> </div><div class=twt-line></div>";
			    
		
		  }
		  
		  $("div#twt_mentions_message").html(DmsData);
		  $("span#mentions_number").html(MentionsCount);
		  
		  if(flag==0)
		  {
		    $("#mentions-more").css("display","none");
		    $("#mentions-text").css("text-align","center");
		 //   $("#mentions-messages").css("overflow-x","hidden");
		  }
		  
		})
    
    
    // get & set statuses data
    $.getJSON(base+"/twitter/getStatuses/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(), ajax: 'true'}, function(j){
		  var message = '';
		  var name = '';
		  var vdate = '';
		  var flag = '';
		  var DmsData = '';
		  
		  
		  
		  for (var i = 0; i < j.length; i++) {
		 
			    message 	= j[i].optionText; 
			    vdate 	= j[i].optionDate;
			  
			   
			    if(vdate)
			    {
				DmsData = "<div class=dms-text id=tweets-text> "+message+"</div> <div class=dms-created_at>"+vdate+"</div>";
			    }
			    else
			    {
				DmsData = "<div class=dms-text id=tweets-text style=text-align:center> "+message+"</div> <div class=dms-created_at>&nbsp;</div>"; 
			    }
		
	        }
		 
		$("div#tweets_update_message").html(DmsData);
		
				  
		})
    
    // get & set tweets data
    $.getJSON(base+"/twitter/getTweets/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(),count:10 , ajax: 'true'}, function(j){
	
	  
		  var message = '';
		  var name = '';
		  var vdate = '';
		  var flag = '';
		  var DmsData = '';
		  var tweetsCount = 0;
	
		  for (var i = 0; i < j.length; i++) {
		 
			message 	= j[i].optionText;
			name  	= j[i].optionName;
			vdate 	= j[i].optionDate;
			vdate 	= j[i].optionDate;
			flag 	= j[i].optionFlag;
			$tweetsCount = j[i].optionCount; 
			   
			DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=statuses-text >"+name+" &nbsp; "+message+"</div> <div class=dms-desc>&nbsp;</div><div class=dms-created_at>"+vdate+"</div></div><div class=twt-line></div>";
		  }
		

		$("div#twt_tweets_message").html(DmsData);
		$("span#tweets_number").html($tweetsCount);
		
		
		if(flag==0)
		{
		   $("#statuses-text").css("text-align","center");
		   $("#tweets-more").css("display","none");
		}
		  
		})
    
    
});
  
  
    /* 
     * jquery function execute on specific event
     */	   
    $(function(){
	
    // on click direct message more link
    $("#dms-more").click(function(){
                  
		$.getJSON(base+"/twitter/getDms/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(), ajax: 'true'}, function(j){
		var message = '';
		var name = '';
		var vdate = '';
		var flag = '';
		var DmsData = '';
		var DmsCount = 0;
		  
		  
		  for (var i = 0; i < j.length; i++) {
		 
			message 	= j[i].optionText; 
			name 	= j[i].optionName;
			vdate 	= j[i].optionDate;
			flag 	= j[i].optionFlag;
			DmsCount= j[i].optionCount;
			   
			   DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=dm-text > "+message+"</div> <div class=dms-desc><a href=http://twitter.com/"+name+" target=_blank>"+name+"</a></div><div class=dms-created_at>"+vdate+"</div></div><div class=twt-line></div>";
			   
   
		  }
		  
		  
		   $("div#twt_dm_message").html(DmsData);
		  
		  if(flag==0)
		  {
		    $("#dms-more").css("display","none");
		  }
		  
		})
                    
          })

    // on click mentions more link
     $("#mentions-more").click(function(){
                  
		$.getJSON(base+"/twitter/getMentions/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(), ajax: 'true'}, function(j){
		var message = '';
		var name = '';
		var vdate = '';
		var flag = '';
		var DmsData = '';
		var MentionsCount= 0;
		  
		  
		  for (var i = 0; i < j.length; i++) {
		 
			message	= j[i].optionText; 
			name 	= j[i].optionName;
			vdate 	= j[i].optionDate;
			flag 	= j[i].optionFlag;
			MentionsCount = j[i].optionCount;
			   
			   DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=mentions-text > "+message+"</div> <div class=dms-desc><a href=http://twitter.com/"+name+" target=_blank>"+name+"</a></div><div class=dms-created_at>"+vdate+"</div> </div><div class=twt-line></div>";
		}
		  
		 
		   $("div#twt_mentions_message").html(DmsData);
		  
		  if(flag==0)
		  {
		    $("#mentions-more").css("display","none");
		  }
		  
		})
                    
          })
     
     // on click mentions more link
     $("#tweets-more").click(function(){
                  $.getJSON(base+"/twitter/getTweets/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(), ajax: 'true'}, function(j){  
		
				
		var message 	= '';
		var name 	= '';
		var vdate 	= '';
		var flag 	= '';
		var DmsData 	= '';
		var tweetsCount = 0;
		  
		for (var i = 0; i < j.length; i++) {
		 
			message 	= j[i].optionText; 
			name 		= j[i].optionName;
			vdate 		= j[i].optionDate;
			flag 		= j[i].optionFlag;
			$tweetsCount 	= j[i].optionCount; 
			   
			      DmsData += "<div class=\"twt-message-block\"><div class=dms-text id=statuses-text >"+name+" &nbsp; "+message+"</div> <div class=dms-created_at>"+vdate+"</div></div><div class=twt-line></div>";
		}
		  
	
		$("div#twt_tweets_message").html(DmsData);
		$("span#tweets_number").html($tweetsCount);
		
		if(flag==0)
		{
		    $("#tweets-text").css("text-align","center");
		    $("#tweets-more").css("display","none");
		}
		  
		})
                    
          })
     
     $("#UsersSubmit").submit(function(){
				$.post($(this).attr('action'), function(html) { 
				$('#form-content').html(html)
				})
			 return false
     })
     
     // update status for twitter , facebook user & pages.
     $("#dashboard-form").submit(function(){
				
				$.post(base+"/twitter/updateStatus/", $(this).serialize() , function(response) {
				    $('#twitter-result').html(response);
				});
				
				$.post(base+"/fbs/updateStatus/", $(this).serialize() , function(response) {
				    $('#facebook-result').html(response);
				});
				
				$.post(base+"/mss/updateStatus/", $(this).serialize() , function(response) {
				    $('#myspace-result').html(response);
				});
				
		  // get & set statuses data
		 setTimeout(null, 5000);

		  $.getJSON(base+"/twitter/getStatuses/",{id: $("input[name='twitterid']").val(),bandid: $("input[name='data[Twitter][bandid]']").val(), ajax: 'true'}, function(j){
		  var message = '';
		  var name = '';
		  var vdate = '';
		  var flag = '';
		  var DmsData = '';
		    message 	= j[0].optionText; 
		    vdate 	= j[0].optionDate;
		  
		   
		    if(vdate)
		    {
			DmsData = "<div class=dms-text id=tweets-text> "+message+"</div> <div class=dms-created_at>"+vdate+"</div>";
		    }
		    else
		    {
			DmsData = "<div class=dms-text id=tweets-text style=text-align:center> "+message+"</div> <div class=dms-created_at>&nbsp;</div>"; 
		    }
		 
		    $("div#tweets_update_message").html(DmsData);
		  
		})
		
		$('#DashboardStatus').val('');

    return false
				
    })
    
    // remember expand / collapse state 
     $("#imageDivLinkup").click(function(){
	 var flag = $("div#imageDivLinkimg").html();
	
	 $.getJSON(base+"/dashboard/updateExpand/",{ id: flag , ajax: 'true'} , function(j){
		  	  
		})
     })
     
    // remember twitter status checkbox state
    $("#DashboardTwt").click(function(){
	 var flag = $('#DashboardTwt').is(':checked');
	$.getJSON(base+"/dashboard/updateTicket/",{ id: flag , name:'twitter', ajax: 'true'} , function(j){
	})
		 
     })
    
    // remember facebook profile status checkbox state
    $("#DashboardFbsProfile").click(function(){
	 var flag = $('#DashboardFbsProfile').is(':checked');
	$.getJSON(base+"/dashboard/updateTicket/",{ id: flag , name:'facebook_profile', ajax: 'true'} , function(j){
	})
		 
     })
    
    // remember twitter status checkbox state
    $("#DashboardFbsPage").click(function(){
	 var flag = $('#DashboardFbsPage').is(':checked');
	$.getJSON(base+"/dashboard/updateTicket/",{ id: flag , name:'facebook_page', ajax: 'true'} , function(j){
	})
		 
     })
    
    // remember twitter status checkbox state
    $("#DashboardMss").click(function(){
	 var flag = $('#DashboardMss').is(':checked');
	$.getJSON(base+"/dashboard/updateTicket/",{ id: flag , name:'mss', ajax: 'true'} , function(j){
	})
		 
     })
	


  });
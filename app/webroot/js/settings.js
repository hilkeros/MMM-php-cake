          function confirms()
          { 
		var answer = confirm("Are you sure you want to delete this from your account?"); 
		if (answer == true)
		{
			return true;
		}
		else
		{
			return false;
		}
          }
          
          
          $(function(){
          // myspace block data updated or Add new profile      
          $("#mssSubmit").click(function(){
                    
                    
                    if($("#MssloginUserId").val())
                    {
                    
                              document.mssAddnew.submit();       
                    }
                    else
                    {
                              document.mssUpdate.submit();       
                    }
            
          })
          
          // Last.fm block data updated or Add new artist 
           $("#lfmSubmit").click(function(){
                   
                                   
                    if($("#LfmMusicGroup").val())
                    {
                    
                              document.lfmsAddnew.submit();       
                    }
                    else
                    {
                              document.lfmUpdate.submit();       
                    }
            
          })
          
          
          $("#setting-tip").click(function(){
                    $("#setting-tip").css('display','none');
                    $.getJSON(base+"/band/updateTip/",{id: 'user-setting', ajax: 'true'}); 
                    
          })
          
	  $("input[name='data[Fb][user]']").change(function(){
	  
		$.getJSON(base+"/band/getpage/",{id: $("input[name='data[Fb][user]']:checked").val(), ajax: 'true'}, function(j){
		  var options = '';
		  for (var i = 0; i < j.length; i++) {
		 
			    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
		  }
		  $("select#FbPage").html(options);
		})
		
		
	  
		$.getJSON(base+"/band/getgroup/",{id: $("input[name='data[Fb][user]']:checked").val(), ajax: 'true'}, function(j){
		  var options = '';
		  for (var i = 0; i < j.length; i++) {
		 
			    options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
		  }
		  $("select#FbGroup").html(options);
		})
		})		
	})

        
	
	$(document).ready(function(){
          
		$(".container").css("height","100%");
                var ids = $("input[name='data[Fb][user]']:checked").val();
                
                if(ids!="none")
                {
                    var page = window.document.getElementById("FbHfbpage").value;
                    var group = window.document.getElementById("FbHfbgroup").value;
                    
                    
                    
                    
                    $.getJSON(base+"/band/getpage/",{id: ids, ajax: 'true'}, function(j){
                    var options = '';
             
                            for (var i = 0; i < j.length; i++)
                            {
                                    if(j[i].optionValue==page)
                                    {
                                            options += '<option value="' + j[i].optionValue + '" SELECTED=SELECTED>' + j[i].optionDisplay + '</option>';
                                    }
                                    else
                                    {
                                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                                    }
                            }
    
                    $("select#FbPage").html(options);
                    })
                    
                     $.getJSON(base+"/band/getgroup/",{id: ids, ajax: 'true'}, function(j){
                    var options = '';
             
                            for (var i = 0; i < j.length; i++)
                            {
                                    if(j[i].optionValue==group)
                                    {
                                            options += '<option value="' + j[i].optionValue + '" SELECTED=SELECTED>' + j[i].optionDisplay + '</option>';
                                    }
                                    else
                                    {
                                            options += '<option value="' + j[i].optionValue + '">' + j[i].optionDisplay + '</option>';
                                    }
                            }
    
                    $("select#FbGroup").html(options);
                    })
                }
		
	})

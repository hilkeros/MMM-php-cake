
  $(document).ready(function(){
	       
    var width= Math.round(screen.width/1.75);
    var height = Math.round(screen.height/1.75);
    $("#statistics").attr("href",$("#statistics").attr("href")+'&width='+width+'&height='+height);

});

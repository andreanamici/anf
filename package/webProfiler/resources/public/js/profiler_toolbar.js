$(document).ready(function(){
    
   $("#profiler-toolbar").click(function(e){      
      if($(this).is(".hidden"))
      {
         e.preventDefault();
         $.post(window.profiler.path_show,function(response){
            if(response.response == true)
            {
               $("#profiler-toolbar").removeClass("hidden");
            }
         },'json');
         return false;
      }
   });
   
   $("#profiler-toolbar-link-hide").click(function(e){
      e.preventDefault();
      var url = $(this).attr("href");
      $.post(url,function(response){
         if(response.response == true)
         {
            $("#profiler-toolbar").addClass("hidden");
         }
      },'json');
      return false;
   });
   
   $("#debug-command-form").submit(function(e){
      e.preventDefault();
      var $commandInput = $(this).find("input[name=command]");
      if($commandInput.val().length > 0)
      {
         $.post($(this).attr("action"),$(this).serialize(),function(response){
            
            if(typeof response.response == "boolean")
            {
               var message = response.response ? "Command execution successfull ^_^" : "Command execution failed -.-'' ";
               alert(message);
            }
            else if(typeof response.response=="string")
            {
               if(typeof $.fancybox == "function")
               {
                    $.fancybox({ 
                      content: response.response,
                      centerOnScroll: true,
                      scrolling: 'yes',
                      width: 800,
                      height: 700,
                      autoScale: false,
                      autoDimensions: false,
                    });
               }
               else 
               {
                   var w = 1600;
                   var h = 800;
                   
                   var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
                   var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

                   var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
                   var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

                   var left = ((width / 2) - (w / 2)) + dualScreenLeft;
                   var top = ((height / 2) - (h / 2)) + dualScreenTop;
                  
                   var w = window.open("",'command',"width="+w+",height="+h+",top="+top+",left="+left);
                   w.document.title = 'Profiler Command';
                   w.document.body.innerHTML = response.response;
               }
            }
            
            $commandInput.val("");
            
         },'json');
      }
      return false;
   });
   
   
});
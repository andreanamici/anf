$(document).ready(function(){
    
     function __stopTail($iframe)
     {
        var interval = parseInt($iframe.attr("data-timeinterval"));

        if(interval>0){
           window.clearInterval(interval);
        }

        return $iframe;
     }

     function __startTail($iframe,now)
     {
        var timeout      = parseInt($iframe.attr("data-timeout-original"));
        var now          = typeof now == "undefined" ? false : now;
        var timeinterval = parseInt($iframe.attr("data-timeinterval"));

        var interval = 0;

        $iframe.closest(".iframe-container").find("input.timeout").val(timeout);


        if(timeinterval > 0){
           window.clearInterval(timeinterval);
        }

        if(timeout > 0){
           var interval = window.setInterval(function($iframe){ __updateIframe($iframe.get(0)); },timeout,$iframe);
        }

        if(now){
           __updateIframe($iframe.get(0));
        }

        $iframe.attr("data-timeinterval",interval);

        return $iframe;
     }


     function __updateIframe(iframe,callback)
     {
         var currWindow = typeof iframe.window !="undefined"   ? iframe.window : iframe.contentWindow;                         
         var callback   = typeof callback       == "function"  ? callback      : function(){  };

         if(typeof currWindow!="undefined")
         {
            var url = iframe.getAttribute("src");
            currWindow.window.onbeforeunload = function(){ callback(); };
            return currWindow.location.href = iframe.getAttribute("src");
         } 
     }

     $(this).keyup(function(e){

        console.log(e.keyCode);

        if(e.keyCode == 27){
           $(".iframe-min").click();
        }
     });

     var timeout = 0;

     $("input.timeout").keyup(function(e){

        if(parseInt($(this).val()) == 0)
        {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest(".iframe-container").find(".iframe-stop").trigger("click");
        }
        else if(parseInt($(this).val()) > 500)
        {
           if(timeout == 0)
           {
              e.preventDefault();
              e.stopPropagation();
              var self = this;

              var fn = function(){
                             var $iframe = $(this).closest(".iframe-container").find("iframe");
                             $iframe.attr("data-timeout-original",$(this).val());
                             __startTail($iframe);
                             timeout = 0;
                       }

              fn = fn.bind(self);

              timeout = window.setTimeout(function(){ fn(); },1000);

           }
        }
     });

     $(".iframe-refresh").click(function(){
        var $iframeContainer = $(this).closest(".iframe-container");
        var $iframe          = $iframeContainer.find("iframe");

        $iframe.closest(".iframe-container").find("a").removeClass("disabled");
        __startTail($iframe,true);
     });

     $(".iframe-stop").click(function(){
        var $iframeContainer = $(this).closest(".iframe-container");
        var $iframe          = $iframeContainer.find("iframe");

        $iframeContainer.find("input.timeout").val("0");
        $iframe.closest(".iframe-container").find("a").removeClass("disabled");
        $(this).addClass("disabled");
        __stopTail($iframe);
     });

     $(".iframe-min").click(function(){
        $(this).closest(".fullscreen").removeClass("fullscreen");
     });

     $(".iframe-full").click(function(){
        var $iframeContainer = $(this).closest(".iframe-container");
        $iframeContainer.addClass("current");
        $(".iframe-container").filter(function(){ return !$(this).is(".current"); }).removeClass("fullscreen");
        $iframeContainer.toggleClass("fullscreen");
     });

     $(".iframe-clear").click(function(e){
          var $iframeContainer = $(this).closest(".iframe-container");
          var $iframe          = $iframeContainer.find("iframe");
          var src              = $iframe.attr("src");

          __stopTail($iframe);
          var srcClear = $iframe.attr("src")+"&clear=1";                          
          $iframe.attr("src",srcClear);

          __updateIframe($iframe.get(0),function(){
               window.setTimeout(function(){ $iframe.attr("src",src); },1000);
          });

     })

     $(".iframe-buttons").dblclick(function(){
           $(this).find(".iframe-full").click();
     });

     $(".iframe-container iframe").each(function(){

           var $iframe = $(this);   
           var timeout = $iframe.attr("data-timeout") ? $iframe.attr("data-timeout") : 0;

           if(parseInt(timeout)>0){
              $(this).closest(".iframe-container").find(".iframe-refresh").addClass("disabled");
           }else{
              $(this).closest(".iframe-container").find(".iframe-stop").addClass("disabled");
           }

           $iframe.attr("data-timeout-original",timeout);
           __startTail($iframe);  

     });
});
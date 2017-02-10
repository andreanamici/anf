<html>
  <head>
    <title>{$title}</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
    window.document.addEventListener( "DOMContentLoaded", function(){                   
        var body  = window.document.body;
        body.onkeyup = function(e)
        {
            var jqp = window.parent.$
            var e = jqp.Event("keyup");
            e.keyCode = 27; // # Some key code value
            jqp(window.parent.document).trigger(e);
        }
    });
    </script>
    <style type="text/css">body pre{color: white;font-family: aller, sans-serif;  font-size: 16px;font-weight: lighter;}</style>
  </head>
  <body>
     <pre>{$logContent}</pre>
</body>
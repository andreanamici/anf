<html>
<head>
    <title>{$title}</title>
    <link rel="stylesheet" href="css/logstailpanel.css" type="text/css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="js/logstailpanel.js"></script>
</head>
   <body>
      
      {loop="logsTypes"}
      
             <div class="iframe-container">
                 <div class="iframe-buttons">
                    <div class="iframe-title">{$value.type|capitalize}</div>
                    <div class="timeout">
                       Refresh time <input type="text" class="timeout" value="" /> ms
                    </div>
                    <ul class="command">
                       <li><a href="#" class="iframe-refresh">[update]</a></li>
                       <li><a href="#" class="iframe-stop">[stop]</a></li>
                       <li><a href="#" class="iframe-clear">[clear]</a></li>
                    </ul>
                    <ul class="window">
                       <li><a href="#" class="iframe-full" title="Zoom">[_]</a></li>
                       <li><a href="#" class="iframe-min" title="Close">[x]</a></li>
                    </ul>
                 </div>
                 <iframe src="{path('_profiler_logstailpanel_view',['logType' => $value1['type'],'lines' => $value1['lines'],'reverse' => $value1['reverse'] ])}" data-timeout="{$refreshTime}"></iframe>
              </div>
      
      {/loop}
   </body>
</html>
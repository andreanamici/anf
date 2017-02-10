<div id="profiler-toolbar" class="{$profiler.status}"> 
   <h2>Debug Toolbar</h2>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Route</div>
      <div class="profiler-toolbar-content">{$profiler.route_name}</div>
   </div>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">i18n</div>
      <div class="profiler-toolbar-content">
         <ul>
            <li>Language: <b>{$profiler.locale}</b>&nbsp;({$profiler.lang})</li>
            <li>Fallback: <b>{$profiler.locale_fallback}</b>&nbsp;({$profiler.lang_fallback})</li>
         </ul>
      </div>
   </div>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">ActionObjects</div>
      <div class="profiler-toolbar-content">
         <ul>
            {$action_objects = $profiler.action_objects}
            {if="$profiler.action_objects.mainactions"}
                {$mainactions    = $action_objects['mainactions']}
                {loop="mainactions"}
                   <li>[MAIN] {$value['actionobject']}::{$value['method']}, {$value['actionobject']->getAbsolutePath()}</li>
                {/loop}
            {/if}
            {if="isset($profiler.action_objects.subactions)"}   
                {$subactions     = $action_objects['subactions']}
                {loop="subactions"}
                   <li>[SUBACTION] {$value['actionobject']}::{$value['method']}, {$value['actionobject']->getAbsolutePath()}</li>
                {/loop}
            {/if}
         </ul>
      </div>
   </div>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Process Time/ Memory</div>
      <div class="profiler-toolbar-content">{$profiler.processtime} / {$profiler.memory_usage}</div>
   </div>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Query executed</div>
      <div class="profiler-toolbar-content">{$profiler.query_number}</div>
   </div>
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Cache</div>
      <div class="profiler-toolbar-content">
         <ul>
            <li>fetched: {$profiler.cachekeys_fetched}</li>
            <li>stored: {$profiler.cachekeys_stored}</li>
         </ul>
      </div>
   </div>
   {if="isset($profiler.hooks)"}
       <div class="profiler-toolbar-element">
        <div class="profiler-toolbar-title">Hooks</div>
        <div class="profiler-toolbar-content">
            <pre style="text-align: left;">
                {$profiler.hooks}
            </pre>
        </div>
     </div>
   {/if}
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Command</div>
      <div class="profiler-toolbar-content">
         <ul>
            <li>
               <form id="debug-command-form" action="{path('_profiler_command')}">
                  <input type="text" name="command" value="" class="required" style="width: 450px;" autocomplete="on" list="commands" />
                  {$commands = $profiler['commands']}
                  {if=" count($commands) > 0 "}
                     <datalist id='commands'>
                        {loop="commands"}
                           <option value='{$value}'>
                        {/loop}
                     </datalist>
                  {/if}
                  <input type="submit" name="send" />
               </form>
            </li>
         </ul>
      </div>
   </div>
   <a href="{$profiler.logstailpanelurl}" title="Go to webTail panel" target='_blank'>
      <div class="profiler-toolbar-element">
         <div class="profiler-toolbar-title">WebTail Panel</div>
         <div class="profiler-toolbar-content">Show logs content</div>
      </div>
   </a>
   <a href="{$profiler.phpinfourl}" title="View PHP info" target='_blank' >
      <div class="profiler-toolbar-element">
         <div class="profiler-toolbar-title">php info</div>
          <div class="profiler-toolbar-content">show php info</div>
      </div>
   </a>
   <a href="{$profiler.prodUrl}" title="View page in production" target='_blank'>
      <div class="profiler-toolbar-element">
          <div class="profiler-toolbar-title">Prod Page</div>
          <div class="profiler-toolbar-content">view page in production</div>
      </div>
   </a>   
   <div class="profiler-toolbar-element">
      <div class="profiler-toolbar-title">Toolbar</div>
      <div class="profiler-toolbar-content"><a href="{path('_profiler_hide')}" id="profiler-toolbar-link-hide">Close</a></div>
   </div>
</div>
<link rel="stylesheet" href="css/profiler_toolbar.css" />
<script type="text/javascript">window.profiler = {$profiler|json}</script>
<script type="text/javascript">
function loadJs(url, success)
{
     var script = document.createElement('script');
     script.src = url;
     var head = document.getElementsByTagName('head')[0],
     done = false;
     head.appendChild(script);
     // Attach handlers for all browsers
     script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
            done = true;
            success();
            script.onload = script.onreadystatechange = null;
            head.removeChild(script);        
        }
     };
}

if (typeof jQuery == 'undefined')
{
    loadJs('http://code.jquery.com/jquery-1.10.2.min.js', function() {
       console.log('jQuery loaded!');
       loadJs('{$rain::$conf['base_url_css']}js/profiler_toolbar.js',function() {
            console.log('profiler loaded!');
       });
    });
}
else
{
    loadJs('{$rain::$conf['base_url_css']}js/profiler_toolbar.js',function() {
       console.log('profiler loaded!');
    });
}
</script>

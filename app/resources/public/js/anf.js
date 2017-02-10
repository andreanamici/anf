/**
 * Anf js v 1.0
 */

(function(w){
    
    /**
     * Anf Routing object
     */
    var AppRouting = function(){

        this.routesBag = {}, 
        this.routingShortcut = {},
        this.baseUrl = "",
        this.basePath = "";

        this.setBaseUrl = function(url){
            this.baseUrl = url;
            return this;
        }

        this.getBaseUrl = function(){
            return this.baseUrl;
        };

        this.getRoute = function(name){
            return typeof this.routesBag[name] != 'undefined' ? this.routesBag[name] : null;
        };

        this.setBasePath = function(path){
            this.basePath = path;
            return this;
        }

        this.getBasePath = function(){
            return this.basePath;
        };


        this.loadRouting = function(routes){
            if(JSON.stringify(this.routesBag) == '{}'){
                this.routesBag = routes
            }else{
                var routesBag= (JSON.stringify(this.routesBag).concat(JSON.stringify(routes))).replace("}{", ",");
                this.routesBag = JSON.parse(routesBag)
            }
            return this;
        }

        this.generateUrl = function(route,params,absolute){

            var route = this.getRoute(route);
            var params = typeof params == 'undefined' ? {} : params;

            if(!route){
                return false;
            }

            var defaults      = typeof route['defaults'] != 'undefined' ? route['defaults'] : {};
            var routeParams   = route.params;
            var path          = route.path;


            for(var param in routeParams)
            {
                if(typeof params[param] == 'undefined')
                {
                    if(typeof defaults[param] == 'undefined')
                    {
                        throw new Error('param '+param+' must be declare!');
                        return false;
                    }
                    
                    if(defaults[params] == null || defaults[params].length == 0)
                    {
                        defaults[params] = '';
                    }
                    
                    if(defaults[params][0] == '@')
                    {
                        throw new Error('this route cannot be rewrite by anf javascript interface becouse use a php service');
                        return false;
                    }
                    
                    params[param] = defaults[params];
                }

                var rule = routeParams[param];

                if(typeof this.routingShortcut[rule] != 'undefined')
                {
                    if(! (""+params[param]).match(this.routingShortcut[rule]))
                    {
                        throw new Error('param "'+param+'" must match rule '+rule);
                        return false;
                    }
                }

                path = path.replace('{'+param+'}',params[param]);
            }

            for(var i in params)
            {
                path = path.replace('(:'+i+')',params[i]);
            }

            var baseUrl = this.baseUrl;

            if(baseUrl[baseUrl.length-1] == '/'){
                baseUrl = baseUrl.substr(0,baseUrl.length-1);
            }

            path = path.replace('//','/');
            path = baseUrl + path;

            if(absolute)
            {
                path = location.protocol+'//'+location.host + path;
            }

            return path;
        };
        
        
        this.redirectToRoute = function(route, params)
        {
            w.location.href =  this.generateUrl(route,params);
        }

        return this;

    };

    /**
     * Anf HttpRequest object
     */
    var AppHttpRequest = function(){

       this.httprequest = {};

       this.__getIndex = function(key, def, prop)
       {
            if(typeof key == 'undefined'){
                return this.httprequest;
            }

            var def = typeof def == 'undefined' ? false : def;

            if(typeof this.httprequest[prop][key] == 'undefined')
            {
                return def;
            }

            return this.httprequest[prop][key];
       }
       
       this.get_post  = function(key, def, prop){
           return this.__getIndex(key, this.__getIndex(key, def, 'POST') , 'GET');
       }
       
       this.get = function(key, def, prop){
           return this.__getIndex(key, def, 'GET');
       }
       
       this.post = function(key, def, prop){
           return this.__getIndex(key, def, 'POST');
       }

       this.loadHttpRequest = function(val){
            if(JSON.stringify(this.httprequest) == '{}'){
                this.httprequest = val
            }else{
                var httprequest = (JSON.stringify(this.httprequest).concat(JSON.stringify(val))).replace("}{", ",");
                this.httprequest = JSON.parse(httprequest);
            }
            return this;
       }

       return this;

    };

    /**
     * Anf Configs object
     */
    var AppConfigs = function(){

       this.configs = {};

       this.get = function(key,def){
            if(typeof key == 'undefined'){
                return this.configs;
            }

            var def = typeof def == 'undefined' ? false : def;

            if(typeof this.configs[key] == 'undefined'){
                return def;
            }

            return this.configs[key];
       };

       this.set = function(key,value){
           this.configs[key] = value;
           return this;
       }

       this.loadConfigs = function(val){
            if(JSON.stringify(this.configs) == '{}'){
                this.configs = configs
            }else{
                var configs = (JSON.stringify(this.configs).concat(JSON.stringify(val))).replace("}{", ",");
                this.configs = JSON.parse(configs);
            }
            return this;
       }

       return this;

    };

    /**
     * Anf Session object
     */
    var AppSession = function(){

       this.data = {};

       this.get = function(key,def){

            if(typeof key == 'undefined'){
                return this.data;
            }

            var def = typeof def == 'undefined' ? false : def;

            if(typeof this.data[key] == 'undefined'){
                return def;
            }

            return this.data[key];
       };

       this.set = function(key,value){
           this.data[key] = value;
       }

       this.loadData = function(val){
            if(JSON.stringify(this.data) == '{}'){
                this.data = val
            }else{
                var data = (JSON.stringify(this.data).concat(JSON.stringify(val))).replace("}{", ",");
                this.data = JSON.parse(data);
            }
            return this;
       }

       return this;

    };

    /**
     * Anf AppLanguage object
     */
    var AppLanguages = function(){

       this.lang = null;

       this.locale = null;

       this.catalogue = {};

       this.translate = function(code, params)
       {
           var value = this.catalogue[code] || code;

           if(typeof params != 'undefined')
           {
               for(var param in params)
               {
                   value = value.replace(param,params[param]);
               }
           }

           return value;
       }

       this.setLanguage = function(val){
           this.lang = val;
           return this;
       }

       this.setLocale = function(val){
           this.locale = val;
           return this;
       }

       this.loadCatalogue = function(val){
            if(JSON.stringify(this.catalogue) == '{}'){
                this.catalogue = val
            }else{
                var catalogue = (JSON.stringify(this.catalogue).concat(JSON.stringify(val))).replace("}{", ",");
                this.catalogue = JSON.parse(catalogue);
            }
            return this;
       }


       return this;

    };

    /**
     * Mobile detector
     */
    var AppMobileDetector = function()
    {
        this.mobileData = {};

        this.isMobile = function(){
            return this.__get('isMobile');
        }

        this.isTablet = function(){
            return this.__get('isTablet');
        }

        this.isIOS = function(){
            return this.__get('isIOS');
        }

        this.isAndroid = function(){
            return this.__get('isAndroid');
        }

        this.isWindowsPhone = function(){
            return this.__get('isWindowsPhone');
        }

        this.version = function(){
            return this.__get('version');
        }

        this.__get = function(name)
        {
            return typeof this.mobileData[name] != "undefined" ? this.mobileData[name] : false;
        }

        this.loadMobileData = function(val){
            if(JSON.stringify(this.mobileData) == '{}'){
                this.mobileData = val
            }else{
                var mobileData = (JSON.stringify(this.mobileData).concat(JSON.stringify(val))).replace("}{", ",");
                this.mobileData = JSON.parse(mobileData);
            }
            return this;
       }
    }

    /**
     * Assets 
     */
    var AppAssets = function()
    {
        this.assetsData = {};

        this.getResourceUrl = function(resource, package)
        {
            return this.getAssetsUrl(package) + '/' + resource; 
        }

        this.getResourcePath = function(resource, package)
        {
            return this.getAssetsPath(package) + '/' + resource; 
        }

        this.getAssetsPath = function(package)
        {
            var packageUrl = typeof package != 'undefined' && package != null ? package : '';
            return anf('routing').getBasePath() + this.assetsData['assetsPath'] + '/' +  packageUrl;
        }

        this.getAssetsUrl = function(package)
        {
            var packageUrl = typeof package != 'undefined' && package != null ? package  : '';
            return anf('routing').getBaseUrl() + this.assetsData['assetsPath'] + '/' + packageUrl;
        }

        this.loadData = function(val){
            if(JSON.stringify(this.assetsData) == '{}'){
                this.assetsData = val
            }else{
                var assetsData = (JSON.stringify(this.assetsData).concat(JSON.stringify(val))).replace("}{", ",");
                this.assetsData = JSON.parse(assetsData);
            }
            return this;
       }
    }

    var AnfApplication = function() {

        var $services = {};

        function __construct()
        {
            $services['routing']          = new AppRouting();
            $services['session']          = new AppSession();
            $services['configs']          = new AppConfigs();
            $services['languages']        = new AppLanguages();
            $services['httprequest']      = new AppHttpRequest();
            $services['mobile_detector']  = new AppMobileDetector();
            $services['assets']           = new AppAssets();

            return this;
        }

        this.get = function(service)
        {
            return typeof $services[service] != "undefined" ? $services[service] : false;
        }
        
        __construct();
        
        return this;
    };
    
    var anfApp = new AnfApplication();
    
    w.anf = function(service)
    {
        return anfApp.get(service);
    }
    
})(window);

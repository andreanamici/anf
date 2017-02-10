if(typeof anf != 'undefined'){
    anf('httprequest').loadHttpRequest({$httprequestData});
}else{
    throw new Error('anf-application must be included!');
}
if(typeof anf != 'undefined'){
    anf('mobile_detector').loadMobileData({$mobileData});
}else{
    throw new Error('anf-application must be included!');
}
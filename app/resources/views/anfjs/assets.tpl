if(typeof anf != 'undefined'){
    anf('assets').loadData({$assetsData});
}else{
    throw new Error('anf-application must be included!');
}
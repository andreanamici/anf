if(typeof anf != 'undefined'){
    anf('configs').loadConfigs({$configsData});
}else{
    throw new Error('anf-application must be included!');
}
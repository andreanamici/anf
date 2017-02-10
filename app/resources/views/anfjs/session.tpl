if(typeof anf != 'undefined'){
    anf('session').loadData({$sessionData})
}else{
    throw new Error('anf must be included!');
}

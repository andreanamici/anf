if(typeof anf != 'undefined'){
    anf('languages').loadCatalogue({$jsonLocale})
                    .setLanguage('{$lang}')
                    .setLocale('{$locale}');
}else{
    throw new Error('anf-application must be included!');
}
if(typeof anf != 'undefined'){
    {if="$baseUrl"} anf('routing').setBaseUrl("{$baseUrl}"){/if}
    {if="$basePath"}anf('routing').setBasePath("{$basePath}");{/if}
    anf('routing').loadRouting({$routes});
}else{
    throw new Error('anf-application must be included!');
}




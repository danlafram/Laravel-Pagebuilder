<?php

use Illuminate\Support\Facades\Route;
use HansSchouten\LaravelPageBuilder\LaravelPageBuilder;

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// handle pagebuilder asset requests
Route::any( config('pagebuilder.general.assets_url') . '{any}', function() {
    $builder = new LaravelPageBuilder(config('pagebuilder'));
    $builder->handlePageBuilderAssetRequest();

})->where('any', '.*');


// handle requests to retrieve uploaded file
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::any( config('pagebuilder.general.uploads_url') . '{any}', function() {
        $builder = new LaravelPageBuilder(config('pagebuilder'));
        logger('handling uploaded file request');
        // logger('tenant: ' . tenant()->getTenantKey());
        logger("storage_path in web.php: " . storage_path());
        $builder->handleUploadedFileRequest();
    })->where('any', '.*');
});



if (config('pagebuilder.website_manager.use_website_manager')) {

    // handle all website manager requests
    Route::any( config('pagebuilder.website_manager.url') . '{any}', function() {

        $builder = new LaravelPageBuilder(config('pagebuilder'));
        $builder->handleRequest();

    })->where('any', '.*');

}


if (config('pagebuilder.router.use_router')) {

    // pass all remaining requests to the LaravelPageBuilder router
    Route::any( '/{any}', function() {
        $builder = new LaravelPageBuilder(config('pagebuilder'));
        $hasPageReturned = $builder->handlePublicRequest();

        if (request()->path() === '/' && ! $hasPageReturned) {
            $builder->getWebsiteManager()->renderWelcomePage();
        }

    })->where('any', '.*');

}

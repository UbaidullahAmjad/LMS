<?php

namespace App\Helpers;

use App\Setting;
use Artesaos\SEOTools\Facades\SEOTools;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class SeoHelper
{ 

    public static function seosettings(){

        try{

            $gsetting = Setting::first();

            SEOTools::setDescription($gsetting->meta_data_desc);
            SEOMeta::addKeyword([$gsetting->meta_data_keyword]);
            SEOTools::opengraph()->setUrl(url('/'));
            SEOTools::setCanonical(url('/'));
            SEOTools::opengraph()->addProperty('type', 'portal');
            SEOTools::twitter()->setSite(url('/'));
            SEOTools::jsonLd()->addImage(url('images/logo/'.$gsetting->logo));
            OpenGraph::addImage(url('images/logo/'.$gsetting->logo));
            SEOMeta::setRobots('all');

        }catch(\Exception $e){

        }
        
    }

}

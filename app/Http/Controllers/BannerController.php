<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function getBanners() {
        return '
            {
                "banners":[
                    {
                    "banner_name":"Play A Game",
                    "banner_file":"/inc/banner.php",
                    "banner_image":"images/PlayAGameBanner.png",
                    "banner_rotations":["comHamster1","PetParkK"],
                    "banner_active" : true,
                    "banner_link" : "http://www.abcmouse.com",
                    "banner_aspect_ratio" : "16x9"
                    },
                    {
                    "banner_name":"Pet Park",
                    "banner_file":"/inc/banner.php",
                    "banner_image":"images/PetParkBanner.png",
                    "banner_rotations":["comdailyKto4","comTestingRotation","comTest"],
                    "banner_active" : false,
                    "banner_link" : "http://www.abcmouse.com",
                    "banner_aspect_ratio" : "16x9"
                    }
                ]
            }
        ';
    }
}

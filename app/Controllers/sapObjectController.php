<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;
require_once ('event.php');

class sapObjectController extends Controller
{

  public function sortapa(Request $request, Response $response)
        {
          $event = array (
            'id_event' => '',
            'country' => 'France',
            'region' => 'Ile De France',
            "postal_code" => 'span[itemprop="postalCode"]',
            "location_name" => 'span[itemprop="addressLocality"]',
            "street_complete" => 'span[itemprop="streetAddress"]',
            'venue_name' => '',
            'event_category' => '',
            'event_type' => '',
            "event_title" => 'h1[itemprop="name"]',
            'event_subtitle' => '',
            'event_intro' => '',                
            "event_description" => 'div.abstract',
            "event_price" => "#practical-info p" ,
            'dates' => '',
            "event_startDate" => '#practical-info p a', 
            "event_endDate" => 'meta[itemprop="endDate"]',


            'opening_hours' => '',
            'languages' => '', 
            // "event_article" => '.article-block',
            "img_thumb" => '#article-carousel',
            "event_site" => '#practical-info p a',
            "event_startHour" => '#practical-info p',
            "event_carousel" => '#article-carousel',
            "thing_to_scrape" => "_text",

            'status' => 1,
            'event_subtitle' => '',
            'event_intro' => '',
            'parthners' => '',                   

            'opening_hours' => "",
            'languages' => "",

            'prices' => '',

            'promoters' => [],
            'date' => null,
            'date_start' => NULL,
            'date_end' => NULL,
            'calendar' => [],
            'additional_info' => '#practical-info p',
          );
                
// object creation
        $ev_obj = new Event($event);
        
        $ev_obj->concerts();
        $ev_obj->theatres();
        $ev_obj->expos();
        $ev_obj->cinemas();
        $ev_obj->spectacles();


         
            
        }    //end sortapa "function"////////////////////////////////



}
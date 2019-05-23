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

class germanytravelController extends Controller
{

  public function germanytravel(Request $request, Response $response)
        {
          $event = array (
            'id_event' => '',
            // 'country' => 'Geramny',
            // 'region' => 'div.location div.eventDate div.date p',//(text)
            "postal_code" => '//*[@id="container"]/div[3]/div[1]/div[2]/div[2]/div[2]/div[4]/div[4]/div/div[1]/p/text()[3]',
            // "location_name" => '',
            // "street_complete" => '',
            // 'venue_name' => '',
            // 'event_category' => '',
            // 'event_type' => '',
            "event_title" => 'div.layoutColumnsInnerRight div h2',//'div h2',//
            // 'event_subtitle' => '',
            // 'event_intro' => '',                
            // "event_description" => '',
            // "event_price" => "" ,
            // 'dates' => '',
            // "event_startDate" => '', 
            // "event_endDate" => '',


            // 'opening_hours' => '',
            // 'languages' => '', 
            // // "event_article" => '',
            // "img_thumb" => '',
            // "event_site" => '',
            // "event_startHour" => '',
            // "event_carousel" => '',
            // "thing_to_scrape" => "",

            // 'status' => 1,
            // 'event_subtitle' => '',
            // 'event_intro' => '',
            // 'parthners' => '',                   

            // 'opening_hours' => "",
            // 'languages' => "",

            // 'prices' => '',

            // 'promoters' => [],
            // 'date' => null,
            // 'date_start' => NULL,
            // 'date_end' => NULL,
            // 'calendar' => [],
            // 'additional_info' => '',
          );
                
// object creation
        $ev_obj = new Event($event);
        
        $ev_obj->concerts();
        // $ev_obj->theatres();
        // $ev_obj->expos();
        // $ev_obj->cinemas();
        // $ev_obj->spectacles();


         
            
        }    //end germanytravel "function"////////////////////////////////



}
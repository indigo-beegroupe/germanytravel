<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

require_once("function_scraper.php");

class ParisInfoController extends Controller
{
    public function basic(Request $request, Response $response)
    {
        $event = array (
            "event_title" => 'h1.Figure-header--title',
            "event_codepostal" => '',
            "event_locality" => '',
            "event_street" => '',
            "event_description" => '',
            "event_site" => '',
            "event_startDate" => '',
            "event_endDate" => '',
            "event_startHour" => '',

            "thing_to_scrape" => "_text"
        );
        /* $client = new Client(); */
        $db_id = "p_conc_";
        $event_links = '';
        $url = array (
        "https://www.parisinfo.com/ou-sortir-a-paris/concerts-a-paris"
        // here we add all the links BUT ONLY for concerts
        );
        /*  self:: */function_scraper($event_links, $url, $event, $db_id);
        /* while($i < 15) */;//((($url."/page/".$i) !==  $url));//when $i exceeds the available number of pages, the site redirects to the first page (page=1)
        //this do -while loop should get all links from all pages, BUT it stops at 20 result (=2 pages)!!

        }    //end basic "function"////////////////////////////////
}
    
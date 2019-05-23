<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

require_once("function_clean.php");


function function_scraper ($event_links, $url_array, $event, $db_id) {
        

  foreach ($url_array as $url)  {
    $i = 2;//start url modification with second page ::: "/page/2"
    $sublinks = [];
    // $test_results = [];
    $id_count = 0; 
    $id_event = "";
    //$x = 0;
    // $page_num =  ($i + $x);

    $client = new Client();

    do {
            /* foreach (range(1, 2) as $x) */ {
              
                $crawler = $client->request('GET', $url);
            
                $links = $crawler->filter($event_links)->each(function ($link) {
                    $links_func = $link->attr('href');
                    return $links_func;
                });
               
                $page_num = ($i + $x);  
                $url = $url."/page/".$page_num; 
                
               foreach( $links as $link_member) {
                            
                            $crawler = $client->request('GET', $link_member);
                            $id_count++;
                            $id_event = $db_id. $id_count;
                
                        //TITLE EXTRACTION CODE    
                            $title = (implode(",", $crawler->filter($event["event_title"])->extract($event["thing_to_scrape"])));
                        
                        //LOCALITY / CITY EXTRACTION CODE    
                            $locality = (implode(",", $crawler->filter($event["event_locality"])->extract($event["thing_to_scrape"])));
                        
                        //CODE POSTALE EXTRACTION CODE        
                            $codepostal = (implode(",", $crawler->filter($event["event_codepostal"])->extract($event["thing_to_scrape"])));
                        
                        //DESCRIPTION EXTRACTION CODE        
                            $description = clean($crawler->filter($event["event_description"])->html());
                    
                        //STREET EXTRACTION CODE        
                            $street = (implode(",", $crawler->filter($event["event_street"])->extract($event["thing_to_scrape"])));
                    
                        //DATE EXTRACTION CODE    
                            //date start    
                            $date_start = $crawler->filter($event["event_startDate"])->attr('content');//                        
                            $date_start = date('Y-m-d', strtotime($date_start));
                            //date end
                            $date_end = $crawler->filter($event["event_endDate"])->attr('content');
                            $date_end = date('Y-m-d', strtotime($date_end));
                        //TIME EXTRACTION CODE    
                            //start time
                            //$startHour NOT WORKING : returns alot of data
                            $startHour = $crawler->filter($event["event_startHour"])->html();//
                            $pieces  = explode("<br>", $startHour, 3);
                            $startHour  = trim($pieces[2]);
                            //$startHour = str_replace("\t", "", $startHour);
                            $startHour = clean($startHour);
                            //$startHour ="start hour";
                        //DISPLAY DETAILS
                            // dump($title, $codepostal, $locality, $description, $site, $street, $date_start, $date_end, $startHour);
                    
                        //COLLECTING LINKS TO INDIVIDUAL PAGES CODE    
                                array_push($sublinks, $link_member);
                            
                                $results[] = [
                                'id' => $id_event,
                                //'date' => $date,
                                'date_start' => $date_start,
                                'date_end' => $date_end,
                                'title' => $title,
                                //'calendar' => $calendar,
                                //'info' => $additional_infos,
                                'text' => $description,
                                //'insert' => $insert_calendar,
                            ];
                    // array_push($test_results, current($results));//trying to extract more than 20 sublinks each time::didn't work ):
                }
                
 
            } 
            sleep(2);
            $i = $i + 1;
            // $x = 1;
           
            // $test_results = $results
        } while ( $i < 7 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
        // dump($test);
        // dump($title);
        dump($sublinks);
        dump($results);  
    }
}




// //for images links'
// $link = $crawler->filter('#product a[data-type="bla"]');

// echo var_dump(count($link));

// var_dump($link->filter('img')->attr('src'));


//innerhtml
//->nodeValue

//images 2 
// imagesCrawler = $crawler->selectImage('Kitten');
// $image = $imagesCrawler->image();

// // or do this all at once
// $image = $crawler->selectImage('Kitten')->image();

// //links
// $linksCrawler = $crawler->selectLink('Go elsewhere...');
// $link = $linksCrawler->link();

// // or do this all at once
// $link = $crawler->selectLink('Go elsewhere...')->link();
// $uri = $link->getUri();
<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

class ParisController extends Controller
{
    

//     public function concerts(Request $request, Response $response)
//     {
//}
public function clean ($item) {
    $cleaned_item = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($item))))));
    return $cleaned_item;
}



public function basic(Request $request, Response $response)
    {

        $url2 = "https://www.sortiraparis.com/scenes/concert-musique/articles/190294-tryo-en-concert-a-l-accorhotels-arena-de-paris-en-mars-2020";
       
                    $event_title = 'h1[itemprop="name"]';
                    $event_codepostal = 'span[itemprop="postalCode"]';
                    $event_locality = 'span[itemprop="addressLocality"]';
                    $event_street = 'span[itemprop="streetAddress"]';
                    $event_description = 'div.abstract';
                    $event_site = '#practical-info p a';
                    $event_startDate = 'meta[itemprop="startDate"]';
                    $event_endDate = 'meta[itemprop="endDate"]';
                    $event_startHour = '#practical-info p';

                    $thing_to_scrape = "_text";
               
      
        $event_links = 'h4 a';
        $url = "https://www.sortiraparis.com/scenes/concert-musique";
        //
        $client = new Client();
        // $crawler = $client->request('GET', $url); 
        
        $i = 0;//start url modification with second page ::: "/page/2"
        $sublinks = [];
        $id_count = 0; 
        $id_event = "";
       do  {
                foreach (range(1, 2) as $x) {
                    // $client = new Client();
                    $crawler = $client->request('GET', $url);
                
                    $page_num = ($i + $x);
                    $url = $url."/page/".$page_num;

                    $links = $crawler->filter($event_links)->each(function ($link) {
                        $links_func = $link->attr('href');
                        return $links_func;
                    });
                    //dump($links);
                   
                  // $i++;


                    foreach( $links as $link_member) {
                                
                                $crawler = $client->request('GET', $link_member);
                                $id_count++;
                                $id_event = "p_conc_". $id_count;//must be changed to conform with each different table[paris,brussels,...etc]
                              
                                $title = /* "title : ". */(implode(",", $crawler->filter($event_title)->extract($thing_to_scrape)));
                                $locality = /* "locality : ". */(implode(",", $crawler->filter($event_locality)->extract($thing_to_scrape)));
                                $codepostal = /* "codepostal : ". */(implode(",", $crawler->filter($event_codepostal)->extract($thing_to_scrape)));
                                $description = $crawler->filter($event_description)->html();//->extract($thing_to_scrape);
                                $description = /* "abstract : ". */self::clean($description);
                                $street = /* "street : ". */(implode(",", $crawler->filter($event_street)->extract($thing_to_scrape)));
                            
                                $date_start = $crawler->filter($event_startDate)->attr('content');//
                                $date_start = /* "start-Date : ". */date('Y-m-d', strtotime($date_start));
                                $date_end = $crawler->filter($event_endDate)->attr('content');
                                $date_end = /* "end-Date : ". */date('Y-m-d', strtotime($date_end));
                            //$tartHour NOT WORKING : returns alot of data
                                $startHour = $crawler->filter($event_startHour)->html();//
                                $pieces  = explode("<br>", $startHour, 3);
                                $startHour  = trim($pieces[2]);
                                //$startHour = str_replace("\t", "", $startHour);
                                $startHour = /* "startHour : ". */self::clean($startHour);
                                //$startHour ="start hour";
                                // dump($title, $codepostal, $locality, $description, $site, $street, $date_start, $date_end, $startHour);
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
                    }
                    sleep(2);
                        
                }   // end foreach links
                 $i = $i + 1;
                 $x = 1;
            }    while($i < 4);
            //dump($links);
            dump($sublinks);
            dump($results);
             //self::scraper($event_links, $url, $event);
         /* while($i < 15) */;//((($url."/page/".$i) !==  $url));//when $i exceeds the available number of pages, the site redirects to the first page (page=1)
            //this do -while loop should get all links from all pages, BUT it stops at 20 result (=2 pages)!!
            
    }   //end while //.//
   }   //end basic "function"////////////////////////////////





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
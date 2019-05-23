<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

//require_once("function_scraper.php");

class AuvergneRhoneAlpesController extends Controller
{
    
    function clean ($item) {
        $cleaned_item = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($item))))));
        return $cleaned_item;
    }

        function function_scraper ($event_links, $url_array, $event, $db_id) {
                

        foreach ($url_array as $url)  {

            $i = 1;//start url modification with second page ::: "/page/2"
            $sublinks = [];
            // $test_results = [];
            $id_count = 0; 
            $id_event = "";
            // $x = 0;
            // $page_num =  ($i + $x);
dump($url);
            $client = new Client();

            do {
                    /* foreach (range(1, 2) as $x) */ {
                    $url = explode("index=".($i-1), $url, 2)[0];
                        $crawler = $client->request('GET', $url);
                    
                        $links = $crawler->filter($event_links)->each(function ($link) {
                            $links_func = $link->attr('href');
                            return $links_func;
                        });
                    
                        $page_num = ($i + $x);  
                        $url = $url."index=".$i; 
                        $url = "https://agenda.brussels/en/concerts/pop-rock.html";
                dump($url);
                    foreach( $url as $link_member) {
                        $crawler = $client->request('GET', $link_member);
                        $id_count++;
                        $id_event = $db_id. $id_count;
            
                    //TITLE EXTRACTION CODE    
                        // $title = (implode(",", $crawler->filter($event["event_title"])->extract($event["thing_to_scrape"])));
                    $title = $crawler->filter('body > p')->eq(0);//('body');//->children('#contenu')->html();
                    dump($title);
                    //LOCALITY / CITY EXTRACTION CODE    
                        $locality = (implode(",", $crawler->filter($event["event_locality"])->extract($event["thing_to_scrape"])));
                    
                    //CODE POSTALE EXTRACTION CODE        
                        $codepostal = (implode(",", $crawler->filter($event["event_codepostal"])->extract($event["thing_to_scrape"])));
                    
                    //DESCRIPTION EXTRACTION CODE        
                        $description = self::clean($crawler->filter($event["event_description"])->html());
                
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
                        $startHour = self::clean($startHour);
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
                } while ( $i < 3 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
                // dump($test);
                dump($title);
                // dump($sublinks);
                // dump($results);  
            }
        }

         //end function_scraper   ////////////////////////////////

    public function concerts(Request $request, Response $response)
    {
       $event = array (
                    "event_title" => 'div#contenu.clearfixh1',
                    "event_codepostal" => '',
                    "event_locality" => '',
                    "event_street" => '#vevent p span.lieu',
                    "event_description" => 'p.accrochePage',
                    "event_site" => '',
                    "event_startDate" => 'span.date',
                    "event_endDate" => '',
                    "event_startHour" => '',

                    "thing_to_scrape" => "_text"
                );
  
        $db_id = "p_thea_";
        $event_links = 'ul li.item.type-agenda div.iteminfo h2 a';
        $url = array (
            "https://www.auvergnerhonealpes.fr/27-recherche-auvergne-rhone-alpes.htm?searchString=concert&"
            );

            self::function_scraper($event_links, $url, $event, $db_id);
         
            
    }   
    
    //end concerts "function"////////////////////////////////


    public function theatre(Request $request, Response $response)
        {
        $event = array (
                        "event_title" => 'h1[itemprop="name"]',
                        "event_codepostal" => 'span[itemprop="postalCode"]',
                        "event_locality" => 'span[itemprop="addressLocality"]',
                        "event_street" => 'span[itemprop="streetAddress"]',
                        "event_description" => 'div.abstract',
                        "event_site" => '#practical-info p:nth-child(2) a',
                        "event_startDate" => 'meta[itemprop="startDate"]',
                        "event_endDate" => 'meta[itemprop="endDate"]',
                        "event_startHour" => '#practical-info p',

                        "thing_to_scrape" => "_text"
                    );
        
            $db_id = "p_conc_";
            $event_links = 'h4 a';
            $url = array (
                "https://www.sortiraparis.com/scenes/concert-musique"
        
                );
        function_scraper($event_links, $url, $event, $db_id);
            
        }    //end theatre "function"////////////////////////////////



    

    public function expos(Request $request, Response $response)
    {
       $event = array (
                    "event_title" => 'h1[itemprop="name"]',
                    "event_codepostal" => 'span[itemprop="postalCode"]',
                    "event_locality" => 'span[itemprop="addressLocality"]',
                    "event_street" => 'span[itemprop="streetAddress"]',
                    "event_description" => 'div.abstract',
                    "event_site" => '#practical-info p:nth-child(2) a',
                    "event_startDate" => 'meta[itemprop="startDate"]',
                    "event_endDate" => 'meta[itemprop="endDate"]',
                    "event_startHour" => '#practical-info p',

                    "thing_to_scrape" => "_text"
                );
  
        $db_id = "p_thea_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/arts-culture/exposition"
            );
            function_scraper($event_links, $url, $event, $db_id);
         
            
    }    //end expos "function"////////////////////////////////
}

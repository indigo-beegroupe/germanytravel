<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;
                    ////
                            // Contenu de ce fichier :
                                //routes : 1.concerts. 2.theatre. 3.expos.
                                //function : 1.function_scraper. 2.clean.
                    ////

class SortirAParisController extends Controller
{
    
// public $url_root = "https://www.sortiraparis.com/";
    public function concerts(Request $request, Response $response)
        {
        $event = array (
                        "event_title" => 'h1[itemprop="name"]',
                        "event_codepostal" => 'span[itemprop="postalCode"]',
                        "event_locality" => 'span[itemprop="addressLocality"]',
                        "event_street" => 'span[itemprop="streetAddress"]',
                        "event_description" => 'div.abstract',
                        "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                        "event_image" => '#article-carousel',
                        "event_site" => '#practical-info p:nth-child(2) a',
                        "event_startDate" => 'meta[itemprop="startDate"]',
                        "event_endDate" => 'meta[itemprop="endDate"]',
                        "event_startHour" => '#practical-info p',
                        "event_carousel" => '#article-carousel',// div.carousel-inner div',
                        "thing_to_scrape" => "_text"
                    );
                    
            $db_id = "p_conc_";
            $event_links = 'h4 a';
            $url = array (
                "https://www.sortiraparis.com/scenes/concert-musique"
        
                );
                self::function_scraper( $event_links, $url, $event, $db_id);
            
        }    //end concerts "function"////////////////////////////////



    public function theatre(Request $request, Response $response)
    {
        $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "event_codepostal" => 'span[itemprop="postalCode"]',
                "event_locality" => 'span[itemprop="addressLocality"]',
                "event_street" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                "event_image" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_carousel" => '#article-carousel',// div.carousel-inner div',
                "thing_to_scrape" => "_text"
                );
  
        $db_id = "p_thea_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/scenes/theatre"
            );
            self::function_scraper($event_links, $url, $event, $db_id);
         
            
    }    //end theatre "function"////////////////////////////////

    

    public function expos(Request $request, Response $response)
    {
       $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "event_codepostal" => 'span[itemprop="postalCode"]',
                "event_locality" => 'span[itemprop="addressLocality"]',
                "event_street" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                "event_image" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_carousel" => '#article-carousel',// div.carousel-inner div',
                "thing_to_scrape" => "_text"
                        );
  
        $db_id = "p_expo_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/arts-culture/exposition"
            );
            self::function_scraper($event_links, $url, $event, $db_id);
         
            
    }    //end expo "function"////////////////////////////////

    public function spectacles(Request $request, Response $response)
    {
       $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "event_codepostal" => 'span[itemprop="postalCode"]',
                "event_locality" => 'span[itemprop="addressLocality"]',
                "event_street" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                "event_image" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',//meta[itemprop="startDate"] OR '#practical-info p a'
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_carousel" => '#article-carousel',// div.carousel-inner div',
                "thing_to_scrape" => "_text"
                        );
  
        $db_id = "p_spect_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/scenes/spectacle"
            );
            self::function_scraper_spect($event_links, $url, $event, $db_id);
         
            
    }    //end spectacle "function"////////////////////////////////

    

    function function_scraper ($event_links, $url_array, $event, $db_id) {
        
        $url_root = "https://www.sortiraparis.com";
        foreach ($url_array as $url)  {
          $i = 2;//start url modification with second page ::: "/page/2"
          $sublinks = [];
          $article = [] ;
          // $test_results = [];
          $id_count = 0; 
          $id_event = "";
          //$x = 0;
          // $page_num =  ($i + $x);
      
          $client = new Client();
          $config = [
            'verify' => false,
        ];

        
        $client->setClient(new \GuzzleHttp\Client($config));
        //   $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
          do {
                  /* foreach (range(1, 2) as $x) */ {
                    
                      $crawler = $client->request('GET', $url);
                  
                      $links = $crawler->filter($event_links)->each(function ($link) {
                          $links_func = $link->attr('href');
                          return $url_root . $links_func;
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
                                $description = self::clean($crawler->filter($event["event_description"])->text());
                            
                              //ARTICLE EXTRACTION CODE        

                                $article_block = ($crawler->filter($event["event_description"])->parents(0)->html());
                                $article = explode("<p>", $article_block);
                                foreach($article as $elem) {
                                    //if
                                    $article .= self::clean($elem);
                                }
                                
                              // MAIN IMAGE EXTRACTION CODE
                              $item = $event["event_image"];
                              $gal = self::image_extract($url_root, $crawler, $item, $flag = false);
                            

                            //// CAROUSEL IMAGE EXTRACTION CODE

                                $carousel = "";
                                $caro = "";
                                $caro_item = "";
                                $caro_gallery = [];
                                $carousel_ex = "";
                                $carousel_ey = "";
                                $carousel_ez = "";
                                $caro_y = "";
                                  $image_block = ($crawler->filter($event["event_carousel"])->html());
                                  $carousel = explode("<img", $image_block);
                                      foreach($carousel as $caro) {
                                          if(substr($caro, 0, 5) == " src=") {
                                            $carousel_ex = $carousel_ex." ". $caro; 
                                        
                                          }
                                      }
                                    $carousel_ez = [];
                                    $str_limit = 0;
                                      $carousel_ey = explode('src="/images/54/0/01-default.jpg"', $carousel_ex);
                                      
                                      foreach($carousel_ey as $arr_elem){
                                            $str_limit = strpos( $arr_elem, "data-lazy");
                                            $arr_elem = ($url_root.(trim(substr($arr_elem, 0, ($str_limit - 2)))));
                                            $arr_elem = str_replace('data-src="', "", $arr_elem);
                                            
                                            array_push($carousel_ez, ($arr_elem));
                                        }
                                     array_shift($carousel_ez);

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
                                      array_push($sublinks, ($url_root . $link_member));
                                  
                                      $results[] = [
                                      'id' => $id_event,
                                    //   'date' => $date,
                                      'date_start' => $date_start,
                                      'date_end' => $date_end,
                                      'title' => $title,
                                      //'calendar' => $calendar,
                                      //'info' => $additional_infos,
                                      'intro' => $description,
                                      'text' => $article,
                                      'image' => $gal, 
                                      
                                      'gallery' => $carousel_ez,
                                      //'insert' => $insert_calendar,
                                  ];
                          // array_push($test_results, current($results));//trying to extract more than 20 sublinks each time::didn't work ):
                      }
                      
       
                  } 
                  sleep(2);
                  $i = $i + 1;

              } while ( $i < 7 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
  
              dump($sublinks);

     
              dump($results);  
        }
    }   /// end function_scraper

    function clean ($item) {
        $cleaned_item = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($item))))));
        return $cleaned_item;
    }


function image_extract ($url_root, $crawler, $item, $flag) {

                              // MAIN IMAGE EXTRACTION CODE
                              $gallery = "";
                              $gal ="";
                              $imgz = "";
                              $caro_gallery =[] ;
                              
                              $caro_item = "";
                                $item_block = ($crawler->filter($item)->html());
                                $image = explode("<img", $item_block);
                                    foreach($image as $img) {
                                        if(substr($img, 0, 5) == " src=") {
                                        $gallery = $gallery ." ". $img; 
                                        // array_push($gal, $img);
                                        }
                                    }
                                    
                                $image_group = explode(" ", $gallery);
                                    foreach($image_group as $imgz) {
                                        if(substr($imgz, 0, 9) == "data-src="){
                                            if(($flag) == false) {
                                                // $gal = $gal ." ".$imgz;
                                                $gal = $url_root ."/".$imgz;
                                                // array_push($gal, $imgz); //??
                                                $gal = str_replace('data-src="/', "", $gal);
                                                $gal = str_replace("\"", "", $gal);
                                                return $gal;

                                            } 
                                        }
                                    }
                                }
}
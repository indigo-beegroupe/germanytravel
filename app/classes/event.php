<?php

namespace App\Controllers;

use PDO;
use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;
require_once ('../bootstrap/app.php');

class Event {
    //members
        //$url_root ?? sent from ?? country ?? event_category ??
    // constructor
public function __construct($event) {

        $this->event = array (
            'id_event' => $event['id_event'],
            'country' => $event['country'],
            'region' => $event['region'],
            "postal_code" => $event['postal_code'],
            "location_name" => $event["location_name"],
            "street_complete" => $event["street_complete"],
            'venue_name' => $event['venue_name'],
            'event_category' => $event['event_category'],
            'event_type' => $event['event_type'],
            "event_title" => $event["event_title"],
            'event_subtitle' => $event['event_subtitle'],
            'event_intro' => $event['event_intro'],                
            "event_description" => $event["event_description"],
            
            'dates' => $event['dates'],
            "event_startDate" => $event["event_startDate"], 
            "event_endDate" => $event["event_endDate"],

            'parthners' => $event['parthners'],


            // "event_article" => '.article-block',
            "img_thumb" => $event["img_thumb"],
            "img_gallery" => '',
            "event_site" => $event["event_site"],
            "event_startHour" => $event["event_startHour"],
            "event_carousel" => $event["event_carousel"],
            "thing_to_scrape" => $event["thing_to_scrape"],
            'event_type' => $event['event_type'],
            'status' => 1,
            'event_subtitle' => $event['event_subtitle'],
            'event_intro' => $event['event_intro'],

            'opening_hours' => $event['opening_hours'],
            'languages' => $event['languages'],
            'event_price' => $event['event_price'],
            'prices' => $event['prices'],
            'promoter' => $event['promoter'],
            'promoters' => $event['promoters'],
            'date' => $event['date'],
            'date_start' => $event['date_start'],
            'date_end' => $event['date_end'],
            'calendar' => $event['calendar'],
            'additional_info' => $event['additional_info'],
          );
    }
//\\// END members

//\\// methods
public function concerts(/* Request $request, Response $response */)
        {                           
            $db_id = "";
            $event_links = 'h4 a';
            $url = array (
                "https://www.sortiraparis.com/scenes/concert-musique"
        
                );
                
            $country = "France";
            $region = "Ile De France";
            $event_category = "music";
            $event_type = "concerts";
            // dump($event);
            // dump($this->event);
           $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
    //these 2 should be called from a different file :: ->
           $this->init_sap_tbl($event_returned);
           $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
           
          //  dump($event_returned);
         
            
        }    //end concerts "function"////////////////////////////////

public function theatres(/* Request $request, Response $response */)
          {
            $db_id = "";
            $event_links = 'h4 a';
            $url = array (
                "https://www.sortiraparis.com/scenes/theatre"
                );
                $country = "France";
                $region = "Ile De France";
                $event_category = "show";
                $event_type = "theatre";
                $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
                //these 2 should be called from a different file :: ->
                       $this->init_sap_tbl($event_returned);
                       $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
            //  dump($event_returned);
           
              
          }    //end theatre "function"////////////////////////////////
        
         

public function expos(/* Request $request, Response $response */)
              {
                                 
                $db_id = "";
                $event_links = 'h4 a';
                $url = array (
                    "https://www.sortiraparis.com/arts-culture/exposition"
                    );
                    $country = "France";
                    $region = "Ile De France";
                    $event_category = "contemporary art";
                    $event_type = "expo";
                    $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
                    //these 2 should be called from a different file :: ->
                           $this->init_sap_tbl($event_returned);
                           $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
                //  dump($event_returned);
               
                  
              }    //end expos "function"////////////////////////////////

public function cinemas(/* Request $request, Response $response */)
              {
                
            
                  $db_id = "";
                  $event_links = 'h4 a';
                  $url = array (
                      "https://www.sortiraparis.com/loisirs/cinema"
                      );
                      $country = "France";
                      $region = "Ile De France";
                      $event_category = "screening";
                      $event_type = "cinema";
                      $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
                      //these 2 should be called from a different file :: ->
                             $this->init_sap_tbl($event_returned);
                             $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
                     
                    //  dump($event_returned);
                   
                      
              }    //end cinema "function"////////////////////////////////


public function spectacles(/* Request $request, Response $response */)
              {
                 
                  $db_id = "";
                  $event_links = 'h4 a';
                  $url = array (
                      "https://www.sortiraparis.com/scenes/spectacle"
                      );
                      $country = "France";
                      $region = "Ile De France";
                      $event_category = "show";
                      $event_type = "spectacle";
                      $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
                      //these 2 should be called from a different file :: ->
                             $this->init_sap_tbl($event_returned);
                             $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
                    //  dump($event_returned);
                      
              }    //end spectacle "function"////////////////////////////////



public  function function_scraper_spect ($event_links, $url_array, $event, $db_id) {
        
  // dump($event);
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
                                      //$id_event = $db_id. $id_count;
    
    
                                  //TITLE EXTRACTION CODE    
                                    $title = (implode(",", $crawler->filter($event["event_title"])->extract($event["thing_to_scrape"])));
                                    $title_hash = base64_encode($title);
                                    if(base64_decode($title_hash) === $title) { //check if the hash is reversible
                                      $image_name = $title_hash; // for images names' stored in local
                                      $id_event = $title_hash; // for stroing in the database
                                    }
                                    
    
                                  //LOCALITY / CITY EXTRACTION CODE    
                                    $location_name = (implode(",", $crawler->filter($event["location_name"])->extract($event["thing_to_scrape"])));
                                  
                                  //CODE POSTALE EXTRACTION CODE        
                                    $codepostal = (implode(",", $crawler->filter($event["postal_code"])->extract($event["thing_to_scrape"])));
                                  
                                  //DESCRIPTION EXTRACTION CODE        
                                    $description = $this->clean_str($crawler->filter($event["event_description"])->text());
                                
                                  //ARTICLE EXTRACTION CODE        
    
                                    $article_block = ($crawler->filter($event["event_description"])->parents(0)->html());
                                    $article = explode("<p>", $article_block);
                                    foreach($article as $elem) {
                                        //if
                                        $article .= $this->clean_str($elem);
                                    }
                                    $article = str_replace ( 'Array', '', $article);
                                    // $article = explode( 'cliquez ici', $article, 2)[0]; 
              
               // remove "cliquez ici + Dernier modification from article
                                $article_end = strlen( $article );
                                if  (strpos( $article, "Et Pour plus de bons plans" )) {
                                  $article_end = (strpos( $article, "Et Pour plus de bons plans" ));
                                } elseif ( (strpos( $article, "Pour plus de bons plans" ))) {
                                  $article_end = (strpos( $article, "Pour plus de bons plans" ));
                                } elseif ( (strpos( $article, "trackReaders" ))) {
                                  $article_end = (strpos( $article, "trackReaders" ));
                                };
                                  $article = substr ($article, 0, $article_end );
                              
                          //PRICe
                                  $event_price_blk = $crawler->filter($event["event_price"])->extract("_text");
                                  foreach ( $event_price_blk as $price_elem ) {
                                        if ( strpos( $price_elem, 'Tarifs' ) )  {
                                              $event_price = $this->clean_str ($price_elem);
                                              $event_price = explode ( 'Tarifs', $event_price, 2 )[1];
                                              if( $event_price != 'Gratuit') {
                                                $event_price .= "\xE2\x82\xAc";
                                          }
                                            }
                                      };
                               
         
                                // MAIN IMAGE EXTRACTION CODE
                                  $item = $event["img_thumb"];
                                  $gal = $this->image_extract($url_root, $crawler, $item, $flag = false);
                                
                                  
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
                                        if($carousel_ey) {  
                                          foreach($carousel_ey as $arr_elem){
                                                $str_limit = strpos( $arr_elem, "data-lazy");
                                                $arr_elem = ($url_root.(trim(substr($arr_elem, 0, ($str_limit - 2)))));
                                                $arr_elem = str_replace('data-src="', "", $arr_elem);
                                                array_push($carousel_ez, ($arr_elem));
                                            }
                                         array_shift($carousel_ez);
                                          }
    // store images locally
                                        //  if ( sizeOf($carousel_ez) > 0 ) {
                                            $i=0;
                                          foreach ($carousel_ez as $img_src) {
                                              $src_file = $img_src;
                                              // $file_name = mt_rand(100, 250);
                                              $dest_file =  dirname(__FILE__).'/../../public/img/'.$image_name.'-'.$i.'.jpg';
                           
                                              $fp = fopen($dest_file, 'w');
                                          
                                              if ( copy($src_file, $dest_file )) {
                                                echo "Copy success!". ++$zyx;
                                                $i++;
                                              } else {
                                                  echo "Copy failed.";
                                              }
                                            }
    
    
                                  //STREET EXTRACTION CODE        
                                      $street = (implode(",", $crawler->filter($event["street_complete"])->extract($event["thing_to_scrape"])));
                        
                                   // official site
                                        $event_site = $crawler->filter($event['event_site'])->extract('href');
                                          foreach($event_site as $temp_site) {
                                              if( (substr($temp_site, 12, 12)!="sortiraparis") && ( $temp_site !="#") ) {
                                                    $site = $temp_site;
                                                  break;
                                              };
                                            }
                                            $event_site = $site;
    
                                    //additional information
                                    // $event_info=""; preg_grep([0-2]\d[h],$event_info)
                                    $event_plus =[];
                                    $event_in_fo = '';
                                    $event_info_title = '';
                                            $event_pl_d_inf  = $crawler->filter($event['additional_info'])->extract('_text');
     
                                              foreach ($event_pl_d_inf as $event_info) {
                                                $event_info = trim( $event_info );
                                                      $event_info_title =  str_replace("\t", "", explode( ' ' ,($event_info), 2 )[0]);
                                                      $event_info_title =  str_replace("\n", "", $event_info_title);
                                                      $event_info =  str_replace("\t", "", explode( ' ' ,($event_info), 2 )[1]);
                                                       if ( strpos($event_info, 'Horaires') ) {
                                                          $event_info_title = "Horaires";
                                                          $event_info = substr($event_info, strpos("Horaires", $event_info), -1);
                                                          $event_plus[$event_info_title] =  str_replace("\n", " . " , $event_info);
                                                        break;
                                                        }
                                                      $event_plus[$event_info_title] =  str_replace("\n", " . " , $event_info);
                                                      
                                                      if(strpos(  $event_plus['Plus'], 'Mis à jour quotidiennement') ) {
                                                        $event_plus['Plus'] = null;
                                                      }
                                                } 
                                                // $event_pl_d_inf = [] ;
                                                
    
                                      // $event_site = $event_site[2];
                                      // get the calling function test
                                      $calling = debug_backtrace()[1]['function']; 
                                      
                                      //DATE EXTRACTION CODE 
                          //\\//\\// if date is calendar then try to find [[hours]] in additional info
                      //\\//\\// if date is simple then try to extract [[hours]] from the same block
    
                                      $date_time_blk = '';  
                                      $date_start = '' ;
                                      $date_end =null; 
                                      $event_startHour = '';
                                      $endHour = '';
                                      $blk_length = '';
                                      $position = '';
                                        //date start    
                                        
                                    if( ($calling == 'cinemas') || ($calling == 'theatres') ) {
                                          $date_start = $crawler->filter($event['event_startDate'])->extract('_text');//attr('content');// 
                                          // $date_start = /* $this->clean_str */(str_replace( 'Du' , '' , $date_start ));
                                          $date_end = explode( 'au', $date_start[0], 2)[1];  
                                          $date_start = explode( 'au', $date_start[0], 2)[0]; 
                                          $date_start = str_replace($date_end, '' , $date_start);
                                        $date_start = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_start ); //$crawler->filter(" header > time")->text()
                                        // $date_start = strtotime('y-m-d', $date_start);
                                        $date_start = date('Y-m-d',strtotime( $date_start ));
                                        
                                     
                                      $date_end = str_replace( 'au', '', $date_end);
                                      $date_end = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', /* 'au', */ 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', /* '', */ '', ''/* , '-' */], $date_end ); //$crawler->filter(" header > time")->text()
                                      $date_end = date('Y-m-d',strtotime( $date_end ));
                                       
                                          /* if($event_startHour == null) */  {
                                            $event_startHour = explode('.', $event_plus["HorairesLe"], 2)[1];
                                              if($event_plus["HorairesLe"] == null) {
                                                $event_startHour = explode('.', $event_plus["Plus"], 2)[1];
                                              } 
                                              if ( ($event_startHour == null) || ($event_startHour == "") ) {
                                                if($event_site) {
                                                  $event_startHour = "Visit the official site : " . $event_site;
                                                }
                                              }
                                          } 
                                          $event_startHour = str_replace(["à", "À", "\r"],["", "", ""],  $event_startHour ); 
                                          $event_startHour = str_replace( ['lundi', 'Lundi', 'mardi', 'Mardi', 'mercredi', 'Mercredi', 'jeudi', 'Jeudi', 'vendredi', 'Vendredi', 'samedi', 'Samedi', 'dimanche', 'Dimanche', 'Du', 'du', 'De', 'de', 'Au', 'au'], ['Monday', 'Monday', 'Tuesday', 'Tuesday', 'Wednesday', 'Wednesday', 'Thursday', 'Thursday', 'Friday', 'Friday', 'Saturday', 'Saturday', 'Sunday', 'Sunday', 'From' , 'from', 'From' , 'from', 'To' ,'to'], $event_startHour );
                                          $event_startHour = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'], $event_startHour );                                  
    
 /* //NOT Cinema or Theatre  */           } else { 
                                              $date_time_blk = $crawler->filter($event['event_startDate'])->text();
                                              $date_time_blk = $this->clean(str_replace( 'Horaires' , '' , $date_time_blk ));
                                              // $date_time_blk = $this->clean(str_replace( 'Du' , '' , $date_time_blk ));
                                              $blk_length = strlen($date_time_blk);
                                              $date_start = $date_time_blk;
    

                                          //date end                              
                                              $date_end = explode( 'au', $date_start, 2)[1];

                                      // convert month name { { in french } } to month number
                                              $date_start = str_replace($date_end, '' , $date_start);
                                              $date_start = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_start ); //$crawler->filter(" header > time")->text()
                                              // $date_start = strtotime('y-m-d', $date_start);
                                              $date_start = date('Y-m-d',strtotime( $date_start ));
                                                
                                            $date_end = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_end ); //$crawler->filter(" header > time")->text()
                                            $date_end = date('Y-m-d',strtotime( $date_end));
                                           
                                             if( $date_end ==  '1970-01-01'){
                                              $date_end =   $date_start;
                                            } 
                                            // } 
         //new time block              // time
                                        
                                            $event_startHour = explode('.', $event_plus["HorairesLe"], 2)[1];
                                              if($event_plus["HorairesLe"] == null) {
                                                $event_startHour = explode('.', $event_plus["Plus"], 2)[1];
                                              } 
                                              if ( ($event_startHour == null) || ($event_startHour == "") ) {
                                                if($event_site) {
                                                  $event_startHour = "Visit the official site : " . $event_site;
                                                }
                                              }
                                          
                                          $event_startHour = str_replace(["à", "À", "\r"],["", "", ""],  $event_startHour );
                                          $event_startHour = str_replace( ['lundi', 'Lundi', 'mardi', 'Mardi', 'mercredi', 'Mercredi', 'jeudi', 'Jeudi', 'vendredi', 'Vendredi', 'samedi', 'Samedi', 'dimanche', 'Dimanche', 'Du', 'du', 'De', 'de', 'Au', 'au'], ['Monday', 'Monday', 'Tuesday', 'Tuesday', 'Wednesday', 'Wednesday', 'Thursday', 'Thursday', 'Friday', 'Friday', 'Saturday', 'Saturday', 'Sunday', 'Sunday', 'From' , 'from', 'From' , 'from', 'To' ,'to'], $event_startHour );
                                        $event_startHour = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'], $event_startHour );                            
                                        } 

                                            $format = "Y-m-d";
                                            $date1  = \DateTime::createFromFormat($format, $date_start);
                                            $date2  = \DateTime::createFromFormat($format, "1970-01-01");
                                            $date3  = \DateTime::createFromFormat($format, $date_end);
                                    if( $date1 == $date2 ) {
                                            // if(strtotime($date_start) == ("1970-01-01") ) {
                                      //dates here !!!
                                          if(!$event_plus['HorairesLe']) {
                                            $date_start = $event_plus['HorairesDu'];
                                          } else {
                                          $date_start = $event_plus['HorairesLe'];
                                          }
                                          $date_start = explode('.', $date_start, 2)[0];
                                          $date_start = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_start ); 
                                          $date_start = date('Y-m-d',strtotime( $date_start ));
                                          
                                    //date_end
                                          // $date_end = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_end ); 
                                          // $date_end = date('Y-m-d',strtotime( $date_end ));
                                          $date_end =   $date_start;
    
                                      
                                            $event_startHour = explode('.', $event_plus["HorairesLe"], 2)[1];
                                              if($event_plus["HorairesLe"] == null) {
                                                $event_startHour = explode('.', $event_plus["Plus"], 2)[1];
                                              } 
                                              if ( ($event_startHour == null) || ($event_startHour == "") ) {
                                                if($event_site) {
                                                    $event_startHour = "Visit the official site : " . $event_site;
                                                }
                                              }
                                           
                                          $event_startHour = str_replace(["à", "À", "\r"],["", "", ""],  $event_startHour );
                                          $event_startHour = str_replace( ['lundi', 'Lundi', 'mardi', 'Mardi', 'mercredi', 'Mercredi', 'jeudi', 'Jeudi', 'vendredi', 'Vendredi', 'samedi', 'Samedi', 'dimanche', 'Dimanche', 'Du', 'du', 'De', 'de', 'Au', 'au'], ['Monday', 'Monday', 'Tuesday', 'Tuesday', 'Wednesday', 'Wednesday', 'Thursday', 'Thursday', 'Friday', 'Friday', 'Saturday', 'Saturday', 'Sunday', 'Sunday', 'From' , 'from', 'From' , 'from', 'To' ,'to'], $event_startHour );
                                          $event_startHour = str_replace( ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'], $event_startHour );                                  
                                           }
                                        //DISPLAY DETAILS
                                            // dump($title, $codepostal, $location_name, $description, $site, $street, $date_start, $date_end, event_startHour);
                                            
                                            //COLLECTING LINKS TO INDIVIDUAL PAGES CODE    
                                            array_push($sublinks, ($url_root . $link_member));
                                                   
                                            $event_mod [] = [
                                                    'id_event' => $id_event,
                                                    'country' => '',
                                                    'region' => '',
                                                    'postal_code' => $codepostal,
                                                    'location_name' => $location_name,
                                                    'street_complete'=> $street,
                                                    'venue_name' => '',
                                                    'event_category' => $event['event_category'],
                                                    'event_type' => $event['event_type'],
                                                    'event_title' => $title,
                                                    'event_subtitle' => '',
                                                    'event_intro' => '',
                                                    'event_description' => $description,
                                                    'price' => $event_price,
                                                    // 'dates' =>'',
                                                    'date_start' => $date_start,//'1970-01-01',//
                                                    'date_end' => $date_end,//'1970-01-01',//
                                                    // 'promoter' => '',
                                                    // 'parthners' => '',
                                                    // 'additional_info' =>'',
                                                    // 'opening_hours' => '',
                                                    // 'languages' => '',
                                                    'img_thumb' => $gal,
                                                    'img_gallery' => $carousel_ez,
                                                    'status' => 1,
                                                    'event_startHour' => $event_startHour,//'',
                                                    'event_endHour' => $endHour,//'',                    
                                                    'calendar' => '',
                                                    'info' => '',
                                                    'site_officiel' => $event_site,
                                                    'additional_info' => $event_plus,
                                                    'calling'=> $calling,
                                                    // 'text' => $article,
                                                   //'insert' => $insert_calendar,
                                                ];
                                            
                                            
                                       // array_push($test_results, current($results));//trying to extract more than 20 sublinks each time::didn't work ):
                                   }
                                   
                    
                               } 
                               sleep(2);
                               $i = $i + 1;
             
                           } while ( $i < 7 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
                  //dump($sublinks);
         dump($event_mod);
         return $event_mod;
                   
            }
        }   /// end function_scraper_spect



public function clean ($item) {
            $cleaned_item =  trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($item))))));
            return $cleaned_item;
        }


public  function clean_str ($item) {
          $cleaned_item =  trim($item);
          $cleaned_item = str_replace("\t", "", $cleaned_item );
          $cleaned_item = str_replace("\n", "", $cleaned_item );
           return $cleaned_item;
      }
    
    
public function image_extract ($url_root, $crawler, $item, $flag) {
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
          
          
public function init_sap_tbl($arr_event) {
            // delete all events beginning with '$db_id_'
                
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "scrapers";
    try {
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
      // set the PDO error mode to exception
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // dump($arr_event);
            foreach($arr_event as $event ) {
              
                $id_event = $event['id_event'];
                $delete_events = $conn->prepare("DELETE FROM `sortiraparis_FR` WHERE `id_event` = '".$id_event."'");
                $delete_events->execute();
            }
          }
            catch(PDOException $e)
    {
    echo "Error: " . $e->getMessage();
    }
    $conn = null;
            return ;
      } //  end init_sap_tbl          function //////////////////////////
    


public function insert_event($arr_event, $country, $region, $event_category, $event_type) {
              
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "scrapers";
    try {
      $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
      // set the PDO error mode to exception
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  foreach($arr_event as $event) {

    //implode additional-info array
    
     {
      $plus = implode(",",$event['additional_info']);
    }
           // insert event
           $insert_event = "REPLACE INTO `sortiraparis_FR`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country , :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type , :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                    
           $sth = $conn->prepare($insert_event);
    
               $sth->bindValue(':id_event', $event['id_event']);
               $sth->bindValue(':country', $country);
               $sth->bindValue(':region', $region);
               $sth->bindValue(':postal_code', $event['postal_code']);
               $sth->bindValue(':location_name', $event['location_name']);
               $sth->bindValue(':street_complete', $event['street_complete']);
               $sth->bindValue(':venue_name', $event['venue_name'] );
               $sth->bindValue(':event_category', $event_category);
               $sth->bindValue(':event_type', $event_type );
               $sth->bindValue(':event_title', $event['event_title'] );
               $sth->bindValue(':event_subtitle', $event['event_subtitle'] );
               $sth->bindValue(':event_intro', $event['event_intro'] );
               $sth->bindValue(':event_description', $event['event_description'] );
               $sth->bindValue(':price', $event['price'] );
               $sth->bindValue(':dates', $event['dates'] );
               $sth->bindValue(':date_start', $event['date_start'] );
               $sth->bindValue(':date_end', $event['date_end'] );
               $sth->bindValue(':promoter', $event['promoter'] );
               $sth->bindValue(':parthners', $event['parthners'] );
               $sth->bindValue(':additional_info', $plus/* $event['additional_info'] */ );
               $sth->bindValue(':opening_hours', $event['opening_hours'] );
               $sth->bindValue(':languages', $event['languages'] );
               $sth->bindValue(':img_thumb', $event['img_thumb'] );
               $sth->bindValue(':img_gallery', $event['img_gallery'] );
               $sth->bindValue(':status', $event['status'] );
    
           $sth->execute();
          }
        }
        catch(PDOException $e)
    {
    echo "Error: " . $e->getMessage();
    }
    $conn = null;
          echo "insert event success";
          // dump($arr_event);
           return ;
        } // end insert_event        function////////////////////////////
    

    
    

//\\// END methods
} //    end class event
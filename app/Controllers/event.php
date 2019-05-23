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
            $event_links = '#container > div.page > div.page-center > div.content-center > div > div.articleList > div:nth-child(1) > div.articleListTeaser > div.articleListHeadline > a';//'div.articleListLink a.internalLink';
            $url = array (
                "https://www.germany.travel/en/events/events/events.html#!/event/search/category/music_shows"
        
                );
                
            $country = "Germany";
            $region = "";
            $event_category = "music";
            $event_type = "concerts";
            // dump($event);
            // dump($this->event);
           $event_returned = $this->function_scraper_spect($event_links, $url, $this->event, $db_id);
    //these 2 should be called from a different file :: ->
          //  $this->init_sap_tbl($event_returned);
          //  $this->insert_event($event_returned, $country, $region, $event_category, $event_type);
           
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
            $url_root = "https://www.germany.travel";
        /* foreach ($url_array as $url) */ 
        $xi = 1; 
        for( $page = 1 ; $page < 2 ; $page++ ) {
$url ='https://www.germany.travel/en/towns-cities-culture/music-shows/musicals-shows/musicals-shows.html#!/event/search/category/music_shows/page/'.$page;
      dump ($url);
              $dowhile = 1;
              $sublinks = [];
              $article = [] ;
              // $test_results = [];
              $id_count = 0; 
              $id_event = "";
              //$x = 0;
              // $page_num =  ($i + $x);
              $client = null;
              $client = new Client();
              $config = [
                'verify' => false,
            ];
    
            $url_pos = strpos($url, '/en/');
            $url_title = substr($url, 0, $url_pos); 
            $client->setClient(new \GuzzleHttp\Client($config));
            //   $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
            
              do {
                      /* foreach (range(1, 2) as $x) */ 
                      $crawler = null;
                          $crawler = $client->request('GET', $url);
                      
                            for($xi = 1; $xi <= 10 ; $xi ++)  {
                                $links_func = $url_title .( $crawler-> filter ('#container div.page div.page-center div.content-center div div.articleList div:nth-child('.$xi.') div.articleListTeaser  div.articleListHeadline a')->attr('href') );
                                array_push($sublinks, ( $links_func));
                                // echo ($url_title . $links_func).'<br>'; 
                            // $xi ++;
                              // /* return  */$url_root . $links_func;
                          }
                        
                          // $page_num = ($i + $x);  
                          // $url = $url."/page/".$page_num; 
                          
                         foreach( $sublinks as $link_member) {
                                      // echo $link_member."</br>";
                                      $crawler = $client->request('GET', $link_member);
                                      // $id_count++;
                                      //$id_event = $db_id. $id_count;
    
    
                          //TITLE EXTRACTION CODE    
                                    $title = $this->clean_Str ( $crawler->filter('div.layoutColumnsInnerRight div h2')->text() );
                                    // $title = sha1( $title[0], false); // sha1 result is 40 character long, NO SPECIAL CHARACTERS!
                                    //SHA1 NOT reversible
                                    // $title = hash( 'sha256',$title[0], false); // result: 64 byte long, No special characters.
                                    // Not reversible!
                                    $image_name = $title;
                                    $id_event = $title;
                                    
    
//                          //LOCALITY / CITY EXTRACTION CODE 
                      
                            if(!empty( ( $crawler -> filter ('#container > div.page > div.page-center > div.content-center > div:nth-child(3) > div.layoutColumnsInnerRight > div.eventData > div.dates > p > strong') ) -> text () ) ){
                              $date_start = [];
                            $dates_blk = ( $crawler -> filter ('#container > div.page > div.page-center > div.content-center > div:nth-child(3) > div.layoutColumnsInnerRight > div.eventData > div.dates > p > strong') ) -> text ();
                                // $date_start = $this->clean_str( $dates_blk )  ;
                                      if($dates_blk == "Upcoming dates:") {
                                        $date_start [] = $crawler -> filter ('div.dates div.eventDate div.date') -> children() ->html ();  // each(function($subDate){})
                                        $date_end/*  = */ ;
                                      }
                                    }
                        
                            if( !empty ( ( $crawler -> filter ('#container > div.page > div.page-center > div.content-center > div:nth-child(3) > div.layoutColumnsInnerRight > div.eventData > div.location > p > strong') ) -> text() ) ) {
                                    $venue_blk = ( $crawler -> filter ('#container > div.page > div.page-center > div.content-center > div:nth-child(3) > div.layoutColumnsInnerRight > div.eventData > div.location > p > strong') ) -> html();
                                    if($venue_blk == "Venue"){
                                      $location_name = ( $crawler->filter('#container > div.page > div.page-center > div.content-center > div:nth-child(3) > div.layoutColumnsInnerRight > div.eventData > div.location > div > div.date')->html());
                                      $venue = $this->clean_str( ( explode("<br>", $location_name,3)[0] ) . ( explode("<br>", $location_name,3)[1] ) );
                                      $venue = str_replace("<p>", '', $venue);
                                      $location_name =$this->clean_str( explode("<br>", $location_name,3)[2] );
                                      $location_name = $this->clean_str( explode( "<br>", $location_name )[0] );
                                      $codepostal = explode( " ", $location_name )[0];
                                      $location_name = explode( " ", $location_name )[1];
                                    }
                                  }
                                    
                                    // $location_name = $this->clean_str( explode( "<br>", $location_name )[2] );

//                          //CODE POSTALE EXTRACTION CODE        
                                    // $address_blk = ($crawler->filter('div.location div.eventData div.date p')->text());
                                    //  $venue_blk;/* $this->clean_Str */

                                    //$codepostal =  ($this->get_numerics( $codepostal))[0];
                         //DATE EXTRACTION CODE                                     
                                    // $date_start = $this->clean_str($address_blk[0]); 
                                    // $date_end = explode(' - ', $date_start, 2)[1];
                                    // $date_start = explode(' - ',$date_start,  2)[0];

                                  
//                            //DESCRIPTION EXTRACTION CODE        
//                                     $description = $this->clean_str($crawler->filter($event["event_description"])->text());
                                
//                            //ARTICLE EXTRACTION CODE        
                           //PRICe
//                   
        
                        // MAIN IMAGE
//                                   $item = $event["img_thumb"];#slideshowTeaser > li.cycle-slide.cycle-slide-active > img
                                  $gal = 'https:'.($crawler->filter('#slideshowTeaser li img')->attr('src'));
                                  $gal = str_replace ( '300sc200', '800sc600', $gal);

                                  
    // store images locally
                                    $src_file = $gal;
                                              $file_name = mt_rand(100, 250);
                                              $dest_file =  dirname(__FILE__).'/../../public/img/'.$image_name.'-'.'0'.'.jpg';
                          
                                              $fp = fopen($dest_file, 'w');
                                          
                                              if ( copy($src_file, $dest_file )) {
                                                echo "Copy success!";
                                                $i++;
                                              } else {
                                                  echo "Copy failed.";
                                              }
//                              // official site
                                        $event_site = $crawler->filter('#container div.page div.page-center div.content-center div:nth-child(3) div.layoutColumnsInnerRight div.eventData div.eventLink a')->extract('href');
//                                                
                              //// CAROUSEL IMAGE 
    
//                            //STREET EXTRACTION CODE        
//                                       $street = (implode(",", $crawler->filter($event["street_complete"])->extract($event["thing_to_scrape"])));
                        

    
//                             //additional information
//                                
                                          $event_plus  = $crawler->filter('#container div.page div.page-center div.content-center div:nth-child(3) div.layoutColumnsInnerRight div.eventData')->extract('_text');
                                          $event_plus  = $this->clean_str(  $event_plus[0]  );
                                          $event_plus  =  substr( $event_plus, 0, ( strpos( $event_plus, 'Events nearby') ) );
                                            if(strpos( $event_plus,'All information') ) {
                                              $event_plus  =  substr( $event_plus, 0, ( strpos( $event_plus, 'All information') ) );
                                            }   
    
//                                       
//                                       $calling = debug_backtrace()[1]['function']; 
                                      
//                                      
//                           
//          //new time block              // time
                                        
//                                             
                                        //DISPLAY DETAILS
                                            // dump($title, $codepostal, $location_name, $description, $site, $street, $date_start, $date_end, event_startHour);
                                            
                                            //COLLECTING LINKS TO INDIVIDUAL PAGES CODE    
                                            // array_push($sublinks, ($url_root . $link_member));
                                            
                                            $event_mod [] = [
                                                    'id_event' => $id_event,
                                                    'country' => 'Germany',
                                                    // 'region' => '',
                                                    'postal_code' => $codepostal,
                                                     'location_name' => $location_name,
                                                  //   'street_complete'=> $street,
                                                    'venue_name' => $venue,
                                                  //   'event_category' => $event['event_category'],
                                                  //   'event_type' => $event['event_type'],
                                                    'event_title' => $title,
                                                  //   'event_subtitle' => '',
                                                  //   'event_intro' => '',
                                                  //   'event_description' => $description,
                                                  //   'price' => $event_price,
                                                  //   // 'dates' =>'',
                                                    'date_start' => $date_start,//'1970-01-01',//
                                                    'date_end' => $date_end,//'1970-01-01',//
                                                  //   // 'promoter' => '',
                                                  //   // 'parthners' => '',
                                                  //   // 'opening_hours' => '',
                                                  //   // 'languages' => '',
                                                    'img_thumb' => $gal,
                                                  //   'img_gallery' => $carousel_ez,
                                                  //   'status' => 1,
                                                  //   'event_startHour' => $event_startHour,//'',
                                                  //   'event_endHour' => $endHour,//'',                    
                                                  //   'calendar' => '',
                                                  //   'info' => '',
                                                    'site_officiel' => $event_site,
                                                    'additional_info' => $event_plus,
                                                  //   'calling'=> $calling,
                                                  //   'text' => $article,
                                                  //  'insert' => $insert_calendar,
                                                ];
                                            
                                            
                                       // array_push($test_results, current($results));//trying to extract more than 20 sublinks each time::didn't work ):
                                  // }
                                    
                                  
                              } 
                              sleep(2);
                              $dowhile++;
            
                           } while ( $dowhile < 2 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
                           //$xi = 1;
                  dump($sublinks);
                  dump($event_mod);
        //  return $event_mod;
                  
            } //end for $xyz

        }   /// end function_scraper_spect


public function get_numerics ($str) {
          preg_match_all('/\d+/', $str, $matches);
          return $matches[0];
      }
public function clean ($item) {
            $cleaned_item =  trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($item))))));
            return $cleaned_item;
        }


public  function clean_str ($item) {
          $cleaned_item =  trim($item);
          $cleaned_item = str_replace("\t", "", $cleaned_item );
          $cleaned_item = str_replace("\n", " ", $cleaned_item );
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
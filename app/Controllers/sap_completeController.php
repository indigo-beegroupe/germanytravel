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
                                //routes : 1.concerts. 2.theatre. 3.expos. 4.cinema. 5.spectacle. 6.get_all.
                                //function : 1.function_scraper. 2.clean. 3.init_sap_tbl. 4.insert_event.
                                        //: 5. image_extract.
                    ////

class sapcompleteController extends Controller
{
  function init_sap_tbl($db_id) {
        // delete all events beginning with '$db_id_'
        $delete_events = "DELETE FROM `sortiraparis_FR` WHERE `id_event` LIKE '".$db_id."%'";
        $del = $this->db->prepare($delete_events);
        $del->execute();
        return ;
  } //  end init_sap_tbl          function //////////////////////////



    function insert_event($arr_event, $country, $region, $event_category, $event_type) {
      foreach($arr_event as $event) {


       // insert event
       $insert_event = "REPLACE INTO `sortiraparis_FR`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country , :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type , :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
       $sth = $this->db->prepare($insert_event);

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
           $sth->bindValue(':additional_info', $event['additional_infos'] );
           $sth->bindValue(':opening_hours', $event['opening_hours'] );
           $sth->bindValue(':languages', $event['languages'] );
           $sth->bindValue(':img_thumb', $event['img_thumb'] );
           $sth->bindValue(':img_gallery', $event['img_gallery'] );
           $sth->bindValue(':status', $event['status'] );

       $sth->execute();
      };
      echo "insert event success";
       return ;
    } // end insert_event        function////////////////////////////

function get_all(Request $request, Response $response){
  self::concerts( $request,$response);
  self::expos($request,$response);
  self::theatres($request,$response);
  self::cinemas($request,$response );
  self::spectacles($request,$response );
}

// public $url_root = "https://www.sortiraparis.com/";
    public function concerts(Request $request, Response $response)
        {
        $event = array (
                        "event_title" => 'h1[itemprop="name"]',
                        "postal_code" => 'span[itemprop="postalCode"]',
                        "location_name" => 'span[itemprop="addressLocality"]',
                        "street_complete" => 'span[itemprop="streetAddress"]',
                        "event_description" => 'div.abstract',
                        "event_article" => '.article-block',
                        "img_thumb" => '#article-carousel',
                        "event_site" => '#practical-info p:nth-child(2) a',
                        "event_startDate" => 'meta[itemprop="startDate"]',
                        "event_endDate" => 'meta[itemprop="endDate"]',
                        "event_startHour" => '#practical-info p',
                        "event_price" => "#practical-info p" ,
                        "event_carousel" => '#article-carousel',
                        "thing_to_scrape" => "_text" ,

                        'event_type' => 'concert',
                        'status' => 1,
                        'event_subtitle' => '',
                        'event_intro' => '',
                        'parthners' => '',                   
                        'additional_infos' => "",
                        'opening_hours' => "",
                        'languages' => "",

                        'price' => "",
                        'prices' => '',
                        'promoter' => "",
                        'promoters' => [],

                        'date' => null,
                        'date_start' => NULL,
                        'date_end' => NULL,
                        'calendar' => [],
                    );
                    
            $db_id = "sap_conc_";
            $event_links = 'h4 a';
            $url = array (
                "https://www.sortiraparis.com/scenes/concert-musique"
        
                );
                
            $country = "France";
            $region = "Ile De France";
            $event_category = "music";
            $event_type = "concerts";
            
            $event_returned = self::function_scraper_spect($event_links, $url, $event, $db_id);
           
           self::init_sap_tbl($db_id);
           self::insert_event($event_returned, $country, $region, $event_category, $event_type);
           
           dump($event_returned);
         
            
        }    //end concerts "function"////////////////////////////////



    public function theatres(Request $request, Response $response)
    {
        $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "postal_code" => 'span[itemprop="postalCode"]',
                "location_name" => 'span[itemprop="addressLocality"]',
                "street_complete" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                "img_thumb" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_price" => "#practical-info p" ,
                "event_carousel" => '#article-carousel',// div.carousel-inner div',
                "thing_to_scrape" => "_text",

                'event_type' => 'theatre',
                'status' => 1,
                'event_subtitle' => '',
                'event_intro' => '',
                'parthners' => '',                   
                'additional_infos' => "",
                'opening_hours' => "",
                'languages' => "",

                'price' => "",
                'prices' => '',
                'promoter' => "",
                'promoters' => [],

                'date' => null,
                'date_start' => NULL,
                'date_end' => NULL,
                'calendar' => [],
                );
  
        $db_id = "sap_thea_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/scenes/theatre"
            );
            $country = "France";
            $region = "Ile De France";
            $event_category = "show";
            $event_type = "theatre";
           $event_returned = self::function_scraper_spect($event_links, $url, $event, $db_id);
           
           self::init_sap_tbl($db_id);
           self::insert_event($event_returned, $country, $region, $event_category, $event_type);
           
           dump($event_returned);
         
            
    }    //end theatre "function"////////////////////////////////

    

    public function expos(Request $request, Response $response)
    {
       $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "postal_code" => 'span[itemprop="postalCode"]',
                "location_name" => 'span[itemprop="addressLocality"]',
                "street_complete" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',/* '.abstract', */ /* 'div.article-block div div div div div div', *///[itemscope][itemtype="http://schema.org/Event"]',
                "img_thumb" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_price" => "#practical-info p" ,
                "event_carousel" => '#article-carousel',// div.carousel-inner div',
                "thing_to_scrape" => "_text" ,

                'event_type' => 'expo',
                'status' => 1,
                'event_subtitle' => '',
                'event_intro' => '',
                'parthners' => '',                   
                'additional_infos' => "",
                'opening_hours' => "",
                'languages' => "",

                'price' => "",
                'prices' => '',
                'promoter' => "",
                'promoters' => [],

                'date' => null,
                'date_start' => NULL,
                'date_end' => NULL,
                'calendar' => [],
                        );
  
        $db_id = "sap_expo_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/arts-culture/exposition"
            );
            $country = "France";
            $region = "Ile De France";
            $event_category = "contemporary art";
            $event_type = "expo";
           $event_returned = self::function_scraper_spect($event_links, $url, $event, $db_id);
           
           self::init_sap_tbl($db_id);
           self::insert_event($event_returned, $country, $region, $event_category, $event_type);
           
           dump($event_returned);
         
            
    }    //end expo "function"////////////////////////////////

    public function cinemas(Request $request, Response $response)
    {
       $event = array (
                "event_title" => 'h1[itemprop="name"]',
                "postal_code" => 'span[itemprop="postalCode"]',
                "location_name" => 'span[itemprop="addressLocality"]',
                "street_complete" => 'span[itemprop="streetAddress"]',
                "event_description" => 'div.abstract',
                "event_article" => '.article-block',
                "img_thumb" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startDate" => 'meta[itemprop="startDate"]',
                "event_endDate" => 'meta[itemprop="endDate"]',
                "event_startHour" => '#practical-info p',
                "event_price" => "#practical-info p" ,
                "event_carousel" => '#article-carousel',
                "thing_to_scrape" => "_text",

                'event_type' => 'cinema',
                'status' => 1,
                'event_subtitle' => '',
                'event_intro' => '',
                'parthners' => '',                   
                'additional_infos' => "",
                'opening_hours' => "",
                'languages' => "",

                'price' => "",
                'prices' => '',
                'promoter' => "",
                'promoters' => [],

                'date' => null,
                'date_start' => NULL,
                'date_end' => NULL,
                'calendar' => [],
                        );
  
        $db_id = "sap_cine_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/loisirs/cinema"
            );
            $country = "France";
            $region = "Ile De France";
            $event_category = "screening";
            $event_type = "cinema";
           $event_returned = self::function_scraper_spect($event_links, $url, $event, $db_id);
           
           self::init_sap_tbl($db_id);
           self::insert_event($event_returned, $country, $region, $event_category, $event_type);
           
           dump($event_returned);
         
            
    }    //end cinema "function"////////////////////////////////

    public function spectacles(Request $request, Response $response)
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
                'promoter' => '',
                'parthners' => '',
                'additional_info' =>'',
                'opening_hours' => '',
                'languages' => '', 
                // "event_article" => '.article-block',
                "img_thumb" => '#article-carousel',
                "event_site" => '#practical-info p:nth-child(2) a',
                "event_startHour" => '#practical-info p',
                "event_carousel" => '#article-carousel',
                "thing_to_scrape" => "_text",
                'event_type' => 'show',
                'status' => 1,
                'event_subtitle' => '',
                'event_intro' => '',
                'parthners' => '',                   
                'additional_infos' => "",
                'opening_hours' => "",
                'languages' => "",
                'price' => "",
                'prices' => '',
                'promoter' => "",
                'promoters' => [],
                'date' => null,
                'date_start' => NULL,
                'date_end' => NULL,
                'calendar' => [],
                );
  
        $db_id = "sap_show_";
        $event_links = 'h4 a';
        $url = array (
            "https://www.sortiraparis.com/scenes/spectacle"
            );
            $country = "France";
            $region = "Ile De France";
            $event_category = "show";
            $event_type = "spectacle";
           $event_returned = self::function_scraper_spect($event_links, $url, $event, $db_id);
           
           self::init_sap_tbl($db_id);
           self::insert_event($event_returned, $country, $region, $event_category, $event_type);
           
           dump($event_returned);
          
         
            
    }    //end spectacle "function"////////////////////////////////


    function function_scraper_spect ($event_links, $url_array, $event, $db_id) {
        
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
                                // if(substr ($title, -3, 3) === 'De '){
                                //     $title = substr ($title, 0, (strlen($title) - 3 ));
                                // };
                                
                              
                              //LOCALITY / CITY EXTRACTION CODE    
                                $location_name = (implode(",", $crawler->filter($event["location_name"])->extract($event["thing_to_scrape"])));
                              
                              //CODE POSTALE EXTRACTION CODE        
                                $codepostal = (implode(",", $crawler->filter($event["postal_code"])->extract($event["thing_to_scrape"])));
                              
                              //DESCRIPTION EXTRACTION CODE        
                                $description = self::clean($crawler->filter($event["event_description"])->text());
                            
                              //ARTICLE EXTRACTION CODE        

                                $article_block = ($crawler->filter($event["event_description"])->parents(0)->html());
                                $article = explode("<p>", $article_block);
                                foreach($article as $elem) {
                                    //if
                                    $article .= self::clean($elem);
                                }
                                $article = str_replace ( 'Array', '', $article);
                                // $article = explode( 'cliquez ici', $article, 2)[0]; 

                               //price 
                              $event_price_blk = $crawler->filter($event["event_price"]) -> extract("_text");
                              foreach ( $event_price_blk as $price_elem ) {
                                    if ( strpos( $price_elem, 'Tarifs' ) )  {
                                      $event_price = self::clean($price_elem);
                                      $event_price = explode ( 'Tarifs', $event_price, 2 )[1];
                                          if( $event_price !== 'Gratuit') {
                                            $event_price .= "\xE2\x82\xAc";
                                          }
                                    }
                              };
                              // MAIN IMAGE EXTRACTION CODE
                              $item = $event["img_thumb"];
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
                                  $street = (implode(",", $crawler->filter($event["street_complete"])->extract($event["thing_to_scrape"])));
                    
                                //DATE EXTRACTION CODE 
                                $date_time_blk = '';  
                                $date_start = '' ;
                                $date_end =null; 
                                $startHour = '';
                                $endHour = '';
                                $blk_length = '';
                                $position = '';
                                  //date start    
                                  ////meta[itemprop="startDate"] OR '#practical-info p a'
                                        $date_time_blk = $crawler->filter('#practical-info p')->text();//attr('content');//                        
                                        $date_time_blk = self::clean(str_replace( 'Horaires' , '' , $date_time_blk ));
                                        // $date_time_blk = self::clean(str_replace( 'Du' , '' , $date_time_blk ));
                                        $blk_length = strlen($date_time_blk);
                                        $date_start = $date_time_blk;

                                    //time_start &&&& time_end
 
                                        if ( $blk_length - strpos( $date_time_blk ,'h') == 1 ) {
                                            $startHour = substr($date_time_blk, -3, 3);
                                        } elseif ( $blk_length - strrpos( $date_time_blk ,'h') == 3 ) {
                                            $startHour = substr($date_time_blk, -5, 5);
                                        } elseif ( ( $blk_length - strpos( $date_time_blk ,'h' ) == 5 ) && ( $blk_length - strrpos( $date_time_blk ,'h' ) == 1 ) )  {
                                            $startHour = substr($date_time_blk, -7, 3);
                                            $endHour = substr($date_time_blk, -3, 3);
                                        } elseif ( ( $blk_length - strpos( $date_time_blk ,'h' ) == 9 ) && ( $blk_length - strrpos( $date_time_blk ,'h' ) == 3 ) )  {
                                            $startHour = substr($date_time_blk, -10, 5);
                                            $endHour = substr($date_time_blk, -5, 5);                                            
                                        } ;
                                    // clean the start date
                                        $date_start = str_replace($startHour, '' , $date_start);
                                        $date_start = str_replace($endHour, '' , $date_start);
                                    //date end                              
                                         $date_end = explode( 'au', $date_start, 2)[1];
                                            // if ($date_end !== null) {
                                            //     $date_end = ('au'.$date_end);
                                            // };
                              // convert month name { { in french } } to month number
                                        $date_start = str_replace($date_end, '' , $date_start);
                                        $date_start = str_replace( ['janvier', 'f vrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'ao t', 'septembre', 'octobre', 'novembre', 'd cembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_start ); //$crawler->filter(" header > time")->text()
                                        // $date_start = strtotime('y-m-d', $date_start);
                                        $date_start = date('Y-m-d',strtotime( $date_start));
                                        
                                      $date_end = str_replace( ['janvier', 'f vrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'ao t', 'septembre', 'octobre', 'novembre', 'd cembre', 'Du', 'au', 'Le', 'le'/* , '/' */], ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', '', '', '', ''/* , '-' */], $date_end ); //$crawler->filter(" header > time")->text()
                                      $date_end = date('Y-m-d',strtotime( $date_end));
                                      if( $date_end ==  '1970-01-01'){
                                        $date_end =   $date_start;
                                      }

                                         
                                                               
                                    //DISPLAY DETAILS
                                        // dump($title, $codepostal, $location_name, $description, $site, $street, $date_start, $date_end, $startHour);
                          
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
                                      'event_category' => 'show',
                                      'event_type' => 'spectacle',
                                      'event_title' => $title,
                                      'event_subtitle' => '',
                                      'event_intro' => '',
                                      'event_description' => $description,
                                      'price' => $event_price,
                                      // 'dates' ='',
                                      'date_start' => $date_start,//'1970-01-01',//
                                      'date_end' => $date_end,//'1970-01-01',//
                                      // 'promoter' => '',
                                      // 'parthners' => '',
                                      // 'additional_info' =>'',
                                      // 'opening_hours' => '',
                                      // 'languages' => '',
                                      'img_thumb' => $gal,
/* ///storing an array ?? */          'img_gallery' => $carousel_ez,
                                      'status' => 1,
                                      'startHour' => '',
                                      'endHour' => '',                           
                                      'calendar' => '',
                                      'info' => '',
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

     //$event = $results;
     return $event_mod;
              //dump($results);  
        }
    }   /// end function_scraper_spect


/*     function time_regex($time) {
        if(preg_match('#^[01]?[0-9]|2[0-3])h$#',$time)){//[0-5][0-9]
            return $time;
        } else {
            return false;
        };
         
    } */


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
                                if(substr ($title, -3, 3) === 'De '){
                                  $title = substr ($title, 0, (strlen($title) - 3 ));
                              };
                              //LOCALITY / CITY EXTRACTION CODE    
                                $location_name = (implode(",", $crawler->filter($event["location_name"])->extract($event["thing_to_scrape"])));
                              
                              //CODE POSTALE EXTRACTION CODE        
                                $codepostal = (implode(",", $crawler->filter($event["postal_code"])->extract($event["thing_to_scrape"])));
                              
                              //DESCRIPTION EXTRACTION CODE        
                                $description = self::clean($crawler->filter($event["event_description"])->text());
                            
                              //ARTICLE EXTRACTION CODE        

                                $article_block = ($crawler->filter($event["event_description"])->parents(0)->html());
                                $article = explode("<p>", $article_block);
                                foreach($article as $elem) {
                                    //if
                                    $article .= self::clean($elem);
                                }
                                $article = str_replace ( 'Array', '', $article);
                                

                            // remove "cliquez ici + Dernier modification from article
                            $article_end = strlen( $article );
                            if  (strpos( $article, "Et Pour plus de bons plans" )) {
                              $article_end = (strpos( $article, "Et Pour plus de bons plans" ));
                            } elseif ( (strpos( $article, "Pour plus de bons plans" ))) {
                              $article_end = (strpos( $article, "Pour plus de bons plans" ));
                            } elseif ( (strpos( $article, "trackReaders" ))) {
                              $article_end = (strpos( $article, "trackReaders" ));
                            };;
                              $article = substr ($article, 0, $article_end );
                          //PRICe
                              $event_price_blk = $crawler->filter($event["event_price"]) -> extract("_text");
                              foreach ( $event_price_blk as $price_elem ) {
                                    if ( strpos( $price_elem, 'Tarifs' ) )  {
                                      $event_price = self::clean($price_elem);
                                      $event_price = explode ( 'Tarifs', $event_price, 2 )[1];
                                      if( $event_price != 'Gratuit') {
                                        $event_price .= "\xE2\x82\xAc";
                                      }
                                    }
                              };
                                
                              // MAIN IMAGE EXTRACTION CODE
                              $item = $event["img_thumb"];
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
                                  $street = (implode(",", $crawler->filter($event["street_complete"])->extract($event["thing_to_scrape"])));
                          
                              //DATE EXTRACTION CODE    
                                  //date start   
                                // //date end                              

                                $date_time_blk = '';  
                                $date_start = '' ;
                                $date_end =null; 
                                $startHour = '';
                                $endHour = '';
                                $blk_length = '';
                                $position = '';
                                  //date start    
                                  ////meta[itemprop="startDate"] OR '#practical-info p a'
                                        $date_time_blk = $crawler->filter('#practical-info p')->text();//attr('content');//                        
                                        $date_time_blk = self::clean(str_replace( 'Horaires' , '' , $date_time_blk ));
                                        
                                        $blk_length = strlen($date_time_blk);
                                        $date_start = $date_time_blk;

                                    //time_start &&&& time_end
 
                                        if ( $blk_length - strpos( $date_time_blk ,'h') == 1 ) {
                                            $startHour = substr($date_time_blk, -3, 3);
                                        } elseif ( $blk_length - strrpos( $date_time_blk ,'h') == 3 ) {
                                            $startHour = substr($date_time_blk, -5, 5);
                                        } elseif ( ( $blk_length - strpos( $date_time_blk ,'h' ) == 5 ) && ( $blk_length - strrpos( $date_time_blk ,'h' ) == 1 ) )  {
                                            $startHour = substr($date_time_blk, -7, 3);
                                            $endHour = substr($date_time_blk, -3, 3);
                                        } elseif ( ( $blk_length - strpos( $date_time_blk ,'h' ) == 9 ) && ( $blk_length - strrpos( $date_time_blk ,'h' ) == 3 ) )  {
                                            $startHour = substr($date_time_blk, -10, 5);
                                            $endHour = substr($date_time_blk, -5, 5);                                            
                                        } ;
                                    // clean the start date
                                        $date_start = str_replace($startHour, '' , $date_start);
                                        $date_start = str_replace($endHour, '' , $date_start);
                                    // remove "De " from the end of $date_start
                                        if(substr ($date_start, (strlen($date_start) - 3 ), 3) === 'De '){
                                          $date_start = substr ($date_start, 0, (strlen($date_start) - 3 ));
                                      };
                                    // date end                              
                                         $date_end = explode( 'au', $date_start, 2)[1];
                                            if ($date_end !== null) {
                                                $date_end = ('au'.$date_end);
                                            };
                                         $date_start = str_replace($date_end, '' , $date_start);
                                 
                          //COLLECTING LINKS TO INDIVIDUAL PAGES CODE    
                                      array_push($sublinks, ($url_root . $link_member));
                                  
                                  //     $results[] = [
                                  //       'id' => $id_event,
                                  //       'title' => $title,
                                  //     // 'date' => $date,
                                  //       'date_start' => $date_start,
                                  //       'date_end' => $date_end,
                                  //         'startHour' => $startHour,
                                  //         'endHour' => $endHour ,
                                  //       'locality' => $location_name,
                                  //       'codepostal' => $codepostal,
                                  //       'street' => $street,
                                  //     //'calendar' => $calendar,
                                  //      //'info' => $additional_infos,
                                  //       'intro' => $description,
                                  //       'text' => $article,
                                  //       'price' => $event_price,
                                  //       'image' => $gal, 
                                        
                                  //       'gallery' => $carousel_ez,
                                  //       //'insert' => $insert_calendar,
                                  // ];
                                  $event_mod [] = [
                                    'id_event' => $id_event,
                                    'country' => '',
                                    'region' => '',
                                    'postal_code' => $codepostal,
                                    'location_name' => $location_name,
                                    'street_complete'=> $street,
                                    'venue_name' => '',
                                    'event_category' => 'show',
                                    'event_type' => 'spectacle',
                                    'event_title' => $title,
                                    'event_subtitle' => '',
                                    'event_intro' => '',
                                    'event_description' => $description,
                                    'price' => $event_price,
                                    // 'dates' ='',
                                    'date_start' => $date_start,//'1970-01-01',//
                                    'date_end' => $date_end,//'1970-01-01',//
                                    // 'promoter' => '',
                                    // 'parthners' => '',
                                    // 'additional_info' =>'',
                                    // 'opening_hours' => '',
                                    // 'languages' => '',
                                    'img_thumb' => $gal,
/* ///storing an array ?? */          'img_gallery' => $carousel_ez,
                                    'status' => 1,
                                    'startHour' => '',
                                    'endHour' => '',                           
                                    'calendar' => '',
                                    'info' => '',
                                    // 'text' => $article,
                                   //'insert' => $insert_calendar,
                                ];
                          // array_push($test_results, current($results));//trying to extract more than 20 sublinks each time::didn't work ):
                      }
                      
       
                  } 
                  sleep(2);
                  $i = $i + 1;

              } while ( $i < 7 ); ///* ((( $url."/page/".$page_num ) !==  $url)) */
  
              // dump($sublinks);
            return($event_mod);
     
              // dump($results);  
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
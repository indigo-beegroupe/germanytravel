<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

class PopRockController extends Controller
{

    public function index(Request $request, Response $response)
    {

        // https://agenda.brussels/api/fr/listEvents/concerts/pop-rock?page=1&place_region=brussels
        // echo Cronos::mediumDate('jeudi 15 janvier 2019');
        // echo Cronos::longDate('15 févr 2019');

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $target_url = "https://agenda.brussels/en/concerts/pop-rock.html";

        foreach (range(1, 2) as $x) {

            $crawler = $client->request('GET', $target_url . "?page=$x");
            
            // Events list // Olivier example
            // $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {
            //     $date = $agendaNode->filter(".thumb__date")->text();
            //     $place = $agendaNode->filter(".thumb__place")->text();
            //     $place = trim(preg_replace('/\s+/', ' ', $place));
            //     $title = $agendaNode->filter("a")->attr('title');
            //     $img = $agendaNode->filter("img")->attr('src');
            //     $img == "/front/images/placeholder.png" ? $img = null : $img;

            //     $link = $agendaNode->filter("a")->attr('href');

            //         // Event page
            //         $client = new Client;
            //         $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
            //         $crawler = $client->request('GET', "https://agenda.brussels" . $link);

            //         $hall    = $crawler->filter('.properties__item--address .properties__subtitle')->text();
            //         $address = $crawler->filter('.properties__item--address address')->html();

            //         $pieces = explode('<br>', $address);
            //         $street = Str::strim($pieces[0]);
            //         $zip = Str::strim($pieces[1]);

            //         $crawler->filter('ul.list--properties li.list__item a')->each(function ($linkNode) use (&$links) {
            //             $links[] = $linkNode->filter("a")->attr('href');
            //         });

            //         foreach ($links as $link) {

            //             if (strpos($link, 'tel:') !== false) {
            //                 $phone = $link;
            //             };
            //             if (strpos($link, 'mailto') !== false) {
            //                 $email = $link;
            //             };
            //             if (strpos($link, 'http') !== false) {
            //                 $site = $link;
            //             };
            //         }

            //         $content = $crawler->filter('.object__text')->html();

            //     $results[] = [
            //         'title'      => trim($title),
            //         'date'       => trim($date),
            //         'place'      => trim($place),
            //         'img'        => trim($img),
            //         'content'    => Str::strim($content),
            //         'hall'       => $hall,
            //         'street'     => $street,
            //         'zip'        => $zip,
            //         'phone'      => @$phone,
            //         'email'      => @$email,
            //         'site'       => @$site,
            //     ];
            // });

            // Marina test
            
            $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                $id_event = 0;

                for($i=0; $i<= (count($results)+1); $i++) {
                    $id_event = $i;
                }

                // foreach ($results as $value) {
                //    $id_event = array_search($value, $results);
                // }

                //echo $id_event .'<br/>';

                // Events list
                $img_thumb = $agendaNode->filter("img")->attr('src');
                $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                $link = $agendaNode->filter("a")->attr('href');
                

                    // Event page
                    $client = new Client;
                    $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                    $crawler = $client->request('GET', "https://agenda.brussels" . $link);


                        $title = $crawler->filter("h1.object__title")->text();
                        //$date = $crawler->filter("h2.object__date")->text();
                        $venue_name = $crawler->filter("li.properties__item--address h5")->text();

                        $address = $crawler->filter('.properties__item--address address')->html();
                        $pieces = explode('<br>', $address);
                        $street = Str::strim($pieces[0]);
                        $zip = Str::strim($pieces[1]);
                        $postal_code = substr($zip, 0, 4);
                        $location_name = substr($zip, 5, 30);

                        $price = "";
                        $prices = '';
                        $promoter = "";
                        $promoters = [];


                        if( $crawler->filter('.properties__item--prices')->count() > 0 ) {

                            if ( $crawler->filter('.properties__item--prices h5.properties__subtitle')->text() == 'Fares' ) {

                                $prices_count = $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count();

                                if ( $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count() > 0 ) {
                                    for( $i=0; $i<$prices_count; $i++ ) {
                                        $prices .= $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->eq($i)->text()."<br/>";
                                    }
                                }

                            }

                            if ( $crawler->filter('.properties__item--prices h5.properties__subtitle')->text() == 'Organiser' ) {
                                
                                if ( $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count() > 0 ) {
                                    $promoter .= $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->text();
                                }
                            }


                        }

                        $description = $crawler->filter('.object__text')->html();


                        $gallery_img = [];

                        if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                            for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                    $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                                }
                            }
                        }

                        $date = null;
                        $date_start = NULL;
                        $date_end = NULL;
                        $calendar = [];

                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = NULL;
                            $date_end .= date('Y-m-d', strtotime($crawler->filter("h2.object__date")->text()));
                            $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $date_end)));
                        }
                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'From' ) !== false ) {
                            $date_complete = $crawler->filter("h2.object__date")->text();
                            $date_start .= date('Y-m-d', strtotime(substr(trim($date_complete), 5, 12)));

                            $date_end .= date('Y-m-d', strtotime(substr(trim($date_complete), 20)));
                        }
                        if ( strpos($crawler->filter("h2.object__date")->text(), 'Until' ) === false && strpos( $crawler->filter("h2.object__date")->text(), 'From' ) === false ) {
                            if( strpos($crawler->filter("h2.object__date")->text(), 'Next' ) !== false ) {
                                $date = date('Y-m-d', strtotime(str_replace('Next date:', '', $crawler->filter("h2.object__date")->text())) );
                            }
                            else {
                                $date = date('Y-m-d', strtotime($crawler->filter("h2.object__date")->text()));
 
                            }
                        }



                        //dates
                        if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                            
                            for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->first()->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->first()->text();
                                        $calendar[$i]["time_start"] = str_replace(array('Start', 'AM', 'PM'), '', $calendar[$i]["time_start"]);

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->first()->text();
                                        $calendar[$i]["time_start"] = str_replace(array('Start', 'AM', 'PM'), '', $calendar[$i]["time_start"]);

                                        $calendar[$i]["time_end"] = $crawler->filter('tr.properties__line')->last()->text();
                                        $calendar[$i]["time_end"] = str_replace(array('End', 'AM', 'PM'), '', $calendar[$i]["time_end"]);

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('tr.properties__line')->first()->text();
                                        $calendar[$i]["time_doors"] = str_replace(array('Doors', 'AM', 'PM'), '', $calendar[$i]["time_doors"]);

                                        $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->last()->text();
                                        $calendar[$i]["time_start"] = str_replace(array('Start', 'AM', 'PM'), '', $calendar[$i]["time_start"]);

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('tr.properties__line')->eq(0)->text();
                                            $calendar[$i]["time_doors"] = str_replace(array('Doors', 'AM', 'PM'), '', $calendar[$i]["time_doors"]);
                                        $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->eq(1)->text();
                                            $calendar[$i]["time_start"] = str_replace(array('Start', 'AM', 'PM'), '', $calendar[$i]["time_start"]);
                                        $calendar[$i]["time_end"] = $crawler->filter('tr.properties__line')->eq(2)->text();
                                            $calendar[$i]["time_end"] = str_replace(array('End', 'AM', 'PM'), '', $calendar[$i]["time_end"]);

                                }

                            }
                            
                        }

                        $country = 'Belgium';
                        $city = 'Brussels Capital Region';
                        $short_city_name = 'Brussels';
                        $event_category = 'music';
                        $event_type = 'concert';
                        $status = 1;
                        $event_subtitle = '';
                        $event_intro = '';
                        $parthners = '';
                        $additional_info = '';
                        $img_gallery = implode(",",$gallery_img);


                $results[] = [
                    'id_event'  => $id_event,
                    'title'      => trim($title),
                    'date'        => date($date),
                    'date_start'  => trim($date_start),
                    'date_end'    => trim($date_end),
                    'venue_name' => trim($venue_name),
                    'street' => $street,
                    'postal_code' => $postal_code,
                    'location' => $location_name,
                    'img' => $img_original,
                    'description' => $description,
                    'price' => $prices,
                    'promoter' => $promoter,
                    'calendar'    => $calendar,
                    'imgs' => implode(",",$gallery_img),
                    'type' => 'concert',
                    'status' => 1,
                ];
                // insert event
                $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `city`, `short_city_name`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :city, :short_city_name, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :img_thumb, :img_gallery, :status )";
        
                $sth = $this->db->prepare($insert_event);

                    $sth->bindValue(':id_event', $id_event);
                    $sth->bindValue(':country', $country);
                    $sth->bindValue(':city', $city);
                    $sth->bindValue(':short_city_name', $short_city_name);
                    $sth->bindValue(':postal_code', $postal_code);
                    $sth->bindValue(':location_name', $location_name);
                    $sth->bindValue(':street_complete', $street);
                    $sth->bindValue(':venue_name', $venue_name );
                    $sth->bindValue(':event_category', $event_category );
                    $sth->bindValue(':event_type', $event_type );
                    $sth->bindValue(':event_title', $title );
                    $sth->bindValue(':event_subtitle', $event_subtitle );
                    $sth->bindValue(':event_intro', $event_intro );
                    $sth->bindValue(':event_description', $description );
                    $sth->bindValue(':price', $prices );
                    $sth->bindValue(':dates', $date );
                    $sth->bindValue(':date_start', $date_start );
                    $sth->bindValue(':date_end', $date_end );
                    $sth->bindValue(':promoter', $promoter );
                    $sth->bindValue(':parthners', $parthners );
                    $sth->bindValue(':additional_info', $additional_info );
                    $sth->bindValue(':img_thumb', $img_original );
                    $sth->bindValue(':img_gallery', $img_gallery );
                    $sth->bindValue(':status', $status );

                $sth->execute();

                // // insert calendar
                $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES (:id_event, :dates, :time_doors, :time_start, :time_end)";
                $sth2 = $this->db->prepare($insert_calendar);

                    $sth2->bindValue(':id_event', $id_event);
                    $sth2->bindValue(':dates', date($calendar[0]['date']));
                    $sth2->bindValue(':time_doors', $calendar[0]['time_doors'] );
                    $sth2->bindValue(':time_start', $calendar[0]['time_start'] );
                    $sth2->bindValue(':time_end', $calendar[0]['time_end'] );
                
                $sth2->execute();

                //delete old events from database
            });


        };
        // echo "<pre>";
        //         var_dump($crawler);
        // comment this to get the view
       dump($results);
      //die();

        //return $this->view->render($response, 'home/index.twig', compact('results', 'mainTitle'));

          
        
        
    }

    // not in use. Example with click function
        // public function click(Request $request, Response $response)
        // {

        //     $config = [
        //         'verify' => false,
        //     ];

        //     $client = new Client;
        //     $client->setClient(new \GuzzleHttp\Client($config));

        //     $crawler = $client->request('GET', "https://agenda.brussels/fr/concerts/pop-rock.html");

        //     $link    = $crawler->filter("a[title='La Grande Guerre de nos écrivains ']")->link();
        //     $crawler = $client->click($link);

        //     $period_date = $crawler->filter('ul.list--properties li.list__item')->first()->text();
        //     $hall        = $crawler->filter('.properties__item--address .properties__subtitle')->text();
        //     $address     = $crawler->filter('.properties__item--address address')->html();
        //     $phone       = $crawler->filter('ul.list--properties li.list__item')->eq(1)->first()->text();
        //     $email       = $crawler->filter('ul.list--properties li.list__item')->eq(2)->first()->text();
        //     $site        = $crawler->filter('ul.list--properties li.list__item')->eq(3)->first()->text();

        //     echo '<h2>' . $period_date . '</h2>';

        //     $content = $crawler->filter('.object__text')->html();
        //     $content = trim(preg_replace('/\s+/', ' ', $content));
        //     echo $content;

        //     echo '<strong>' . $hall . '</strong>';
        //     echo '<br>';
        //     echo $address;
        //     echo $phone;
        //     echo '<br>';
        //     echo $email;
        //     echo '<br>';
        //     echo $site;

        //     // return $this->view->render($response, 'home/index.twig', compact('results', 'mainTitle'));

        // }

    // use agenda.brussels API to get Wallonia and Flanders data
        // public function agendaApi(Request $request, Response $response)
        // {

        //     $url = "https://agenda.brussels/api/fr/listEvents/concerts/pop-rock?page=1&place_region=wallonia";

        //     $config = [
        //         'verify' => false,
        //     ];

        //     $client = new Client;
        //     $client->setClient(new \GuzzleHttp\Client($config));
        //     $crawler = $client->request('GET', $url);
        //     $json    = $client->getResponse()->getContent();

        //     $html = json_decode($json);
        //     $html = $html->data;

        //     $crawler = new Crawler($html);
        //     $crawler->filter('.list__item')->each(function ($agendaNode) use (&$results) {

        //         $date  = Cronos::mediumDate($agendaNode->filter(".thumb__date")->text());
        //         $place = Str::strim($agendaNode->filter(".thumb__place")->text());
        //         $title = $agendaNode->filter("a")->attr('title');
        //         $img   = $agendaNode->filter("img")->attr('src');
        //         $link  = $agendaNode->filter('.thumb--agenda')->attr('href');

        //         $config = [
        //             // 'proxy'  => 'malotol:ma21ol@147.67.117.13:8012',
        //             'verify' => false,
        //         ];

        //         $client = new Client;
        //         $client->setClient(new \GuzzleHttp\Client($config));
        //         $crawler = $client->request('GET', "https://agenda.brussels" . $link);
        //         $hall    = $crawler->filter('.properties__item--address .properties__subtitle')->text();
        //         $address = Str::strim($crawler->filter('.properties__item--address address')->html());

        //         $crawler->filter('ul.list--properties li.list__item a')->each(function ($linkNode) use (&$links) {
        //             $links[] = $linkNode->filter("a")->attr('href');
        //         });

        //         foreach ($links as $link) {

        //             if (strpos($link, 'tel:') !== false) {
        //                 $phone = $link;
        //             };
        //             if (strpos($link, 'mailto') !== false) {
        //                 $email = $link;
        //             };
        //             if (strpos($link, 'http') !== false) {
        //                 $site = $link;
        //             };
        //         }

        //         $content = $crawler->filter('.object__text')->html();
        //         $content = Str::strim($content);

        //         $results[] = [
        //             'title'   => trim($title),
        //             'date'    => trim($date),
        //             'place'   => trim($place),
        //             'img'     => trim($img),
        //             'link'    => trim($link),
        //             'content' => $content,
        //             'hall'    => $hall,
        //             'address' => $address,
        //             'phone'   => @$phone,
        //             'email'   => @$email,
        //             'site'    => @$site,
        //         ];
        //     });

        //     dump($results);
        // }

}



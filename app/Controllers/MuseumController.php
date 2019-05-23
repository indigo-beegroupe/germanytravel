<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

class MuseumController extends Controller
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

        $target_url = "https://agenda.brussels/en/exhibitions/museums-and-art-ce.html";

        foreach (range(1, 1) as $x) {

            $crawler = $client->request('GET', $target_url . "?page=$x");

            $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                // Events list
                $img_thumb = $agendaNode->filter("img")->attr('src');
                $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                $link = $agendaNode->filter("a")->attr('href');
                

                    // Event page
                    $client = new Client;
                    $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                    $crawler = $client->request('GET', "https://agenda.brussels" . str_replace("\/", '/', $link) );


                        $title = $crawler->filter("h1.object__title")->text();
                       
                        $venue_name = $crawler->filter("li.properties__item--address h5")->text();

                        $address = $crawler->filter('.properties__item--address address')->html();
                        $pieces = explode('<br>', $address);
                        $street = Str::strim($pieces[0]);
                        $zip = Str::strim($pieces[1]);
                        $postal_code = substr($zip, 0, 4);
                        $location_name = substr($zip, 5, 30);

                        $price = "";
                        $prices = [];
                        $promoter = "";
                        $promoters = [];

                        $videos = [];
                        $gallery_img = [];


                        if( $crawler->filter('.properties__item--prices')->count() > 0 ) {

                            if ( $crawler->filter('.properties__item--prices h5.properties__subtitle')->text() == 'Fares' ) {

                                $prices_count = $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count();

                                if ( $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count() > 0 ) {
                                    for( $i=0; $i<$prices_count; $i++ ) {
                                        $prices[] = $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->eq($i)->text();
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


                        if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                            for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                    $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                                }
                                
                            }
                        }

                        $date = "";
                        $date_start = "";
                        $date_end = "";
                        $calendar = [];

                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = '';
                            $date_end .= $crawler->filter("h2.object__date")->text();
                            $date_end = str_replace('Until', '', $date_end);
                        }
                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'From' ) !== false ) {
                            $date_complete = $crawler->filter("h2.object__date")->text();
                            $date_start .= substr(trim($date_complete), 5, 12);

                            $date_end .= substr(trim($date_complete), 20);
                        }
                        if ( strpos($crawler->filter("h2.object__date")->text(), 'Until' ) === false && strpos( $crawler->filter("h2.object__date")->text(), 'From' ) === false ) {
                            if( strpos($crawler->filter("h2.object__date")->text(), 'Next' ) !== false ) {
                                $date .= str_replace('Next date:', '', $crawler->filter("h2.object__date")->text());
                            }
                            else {
                                $date .= $crawler->filter("h2.object__date")->text();
                            }
                        }




                        if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                            
                            for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {


                                if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                    $calendar[$i]["date"] = $crawler->filter("li.properties__item--opening h5")->first()->text();

                                    if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                        if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                            $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->first()->text();
                                            $calendar[$i]["time_start"] = str_replace('Start', '', $calendar[$i]["time_start"]);
                                        }  
                                    }

                                    if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                        if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                            $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->first()->text();
                                            $calendar[$i]["time_start"] = str_replace('Start', '', $calendar[$i]["time_start"]);

                                            $calendar[$i]["time_end"] = $crawler->filter('tr.properties__line')->last()->text();
                                            $calendar[$i]["time_end"] = str_replace('End', '', $calendar[$i]["time_end"]);
                                        }

                                        if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                            $calendar[$i]["time_doors"] = $crawler->filter('tr.properties__line')->first()->text();
                                            $calendar[$i]["time_doors"] = str_replace('Doors', '', $calendar[$i]["time_doors"]);

                                            $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->last()->text();
                                            $calendar[$i]["time_start"] = str_replace('Start', '', $calendar[$i]["time_start"]);
                                        }
                                    }

                                    if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                            $calendar[$i]["time_doors"] = $crawler->filter('tr.properties__line')->eq(0)->text();
                                                $calendar[$i]["time_doors"] = str_replace('Doors', '', $calendar[$i]["time_doors"]);
                                            $calendar[$i]["time_start"] = $crawler->filter('tr.properties__line')->eq(1)->text();
                                                $calendar[$i]["time_start"] = str_replace('Start', '', $calendar[$i]["time_start"]);
                                            $calendar[$i]["time_end"] = $crawler->filter('tr.properties__line')->eq(2)->text();
                                                $calendar[$i]["time_end"] = str_replace('End', '', $calendar[$i]["time_end"]);

                                    }

                                }

                            }
                                if( $crawler->filter("li.properties__item--opening h5")->first()->text() == "Dates" ) {
                                    $date_complete = $crawler->filter('ul.list--properties')->first()->text();

                                    $date_start .= substr(trim($date_complete), 5, 12);
                                }

                                $opening_hours = [];

                                if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                    for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                        $opening_hours[] = $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text();

                                    }
                                }
                        }

                        $languages = [];

                        if( $crawler->filter("li.properties__item--languages")->count() > 0 ) {
                            
                            for($j=0; $j<$crawler->filter('li.properties__item--languages ul li')->count(); $j++) {

                                $languages[] = $crawler->filter("li.properties__item--languages ul li")->eq($j)->text();

                            }
                        }


                $results[] = [
                    'title'       => trim($title),
                    'date'        => trim($date),
                    'date_start'  => trim($date_start),
                    'date_end'    => trim($date_end),
                    'venue_name'  => trim($venue_name),
                    'street'      => $street,
                    'postal_code' => $postal_code,
                    'location'    => $location_name,
                    'city'        => 'Brussels Capital Region',
                    'img'         => $img_original,
                    'description' => $description,
                    'price'       => $prices,
                    'promoter'    => $promoter,
                    'imgs'        => $gallery_img,
                    'calendar'    => $calendar,
                    'opening_hours' => $opening_hours,
                    'type' => 'exhibition',
                    'languages' => $languages,
                ];
            });
        };
        // echo "<pre>";
        //         var_dump($crawler);
        // comment this to get the view
       dump($results);
       die();

        return $this->view->render($response, 'home/index.twig', compact('results', 'mainTitle'));
    }

}

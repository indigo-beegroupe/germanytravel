<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

class BrusselsController extends Controller
{

    public function concerts(Request $request, Response $response)
    {
        // delete all events beginning with 'conc_'
                $delete_concerts = "DELETE FROM `agenda.brussels_bxl` WHERE `id_event` LIKE 'b_conc_%'";
                $del = $this->db->prepare($delete_concerts);
                $del->execute();

        // start scraping

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $concert_urls = [
            "https://agenda.brussels/en/concerts/pop-rock.html",
            // "https://agenda.brussels/en/concerts/chanson.html",
            // "https://agenda.brussels/en/concerts/jazz-blues.html",
            // "https://agenda.brussels/en/concerts/worldmusic.html",
            // "https://agenda.brussels/en/concerts/electro-dj.html",
            // "https://agenda.brussels/en/concerts/classical-music.html",
            // "https://agenda.brussels/en/concerts/contemporary-music.html"
        ];

        $id_count = 0; 
        $id_event = "";


        foreach (range(1, 2) as $x) {

            foreach( $concert_urls as $concert_url ) {

                

                $crawler = $client->request('GET', $concert_url . "?page=$x");


                $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                    for($i = $id_count; $i <= (count($results)); $i++) {
                        $id_count++;
                        $id_event = 'b_conc_' . $id_count;

                    }


                        // Events list
                    $img_thumb = $agendaNode->filter("img")->attr('src');
                    $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                    $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                    $link = $agendaNode->filter("a")->attr('href');
                

                    // Event page
                    $client = new Client;
                    $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                    $crawler = $client->request('GET', "https://agenda.brussels" . $link);

                    // extract title + format text
                        $title = str_replace("/", " - ", $crawler->filter("h1.object__title")->text() );
                        //$date = $crawler->filter("h2.object__date")->text();
                    
                    //extract venue name + format text    
                        $venue_name = $crawler->filter("li.properties__item--address h5")->text();
                    
                    //extract address + format html
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

                        $date = null;
                        $date_start = NULL;
                        $date_end = NULL;
                        $calendar = [];

                        $description = str_replace( '<p><i>The content of this event is not available in your language.</i></p>', '', $crawler->filter('.object__text')->html() );

                        $gallery_img = [];

                        $country = 'Belgium';
                        $region = 'Brussels Capital Region';


                        if ( strpos( $crawler->filter("h4.properties__title")->text(), 'Classical' ) !== false ) {
                            $event_category = 'classical music';
                        }
                        else {
                            $event_category = 'music';
                        }


                        $event_type = 'concert';
                        $status = 1;
                        $event_subtitle = '';
                        $event_intro = '';
                        $parthners = '';
                        
                        $additional_infos = "";
                        $opening_hours = "";
                        $languages = "";

                        if( $crawler->filter(".properties__item--languages ul.list--properties li.list__item") !== NULL ) {
                            for($j=0; $j<$crawler->filter(".properties__item--languages ul.list--properties li.list__item")->count(); $j++) {
                                $languages .= trim( $crawler->filter(".properties__item--languages ul.list--properties li.list__item")->eq($j)->text() ) . '<br/>';
                            }
                        }

                        $img_gallery = '';

                    // extract price / prices
                        if( $crawler->filter('.properties__item--prices')->count() > 0 ) {

                            if ( $crawler->filter('.properties__item--prices h5.properties__subtitle')->text() == 'Fares' ) {

                                $prices_count = $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count();

                                if ( $crawler->filter('.properties__item--prices')->first()->filter('ul.list--properties li.list__item')->count() > 0 )
                                /* if ($prices_count>0) */ {
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
                    
                    // get the images' links 
                        if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                            for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                    $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                                }
                            }

                            $img_gallery = implode(", ",$gallery_img);
                        }

                        
                    // extract dates (start, end)
                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = NULL;
                            $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $crawler->filter("h2.object__date")->text())));
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



                    //store dates -> Calendar
                    if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                        
                        for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                            if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->eq($i)->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));

                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(0)->text();
                                            $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(1)->text();
                                            $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));
                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(2)->text();
                                            $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                }

                            }

                            if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                    $opening_hours .= trim( $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() );

                                }
                            }

                        }
                        
                    }



                // insert event
                    $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) 
                    VALUES 
                    (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
                        $sth = $this->db->prepare($insert_event);

                            $sth->bindValue(':id_event', $id_event);
                            $sth->bindValue(':country', $country);
                            $sth->bindValue(':region', $region);
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
                            $sth->bindValue(':additional_info', $additional_infos );
                            $sth->bindValue(':opening_hours', $opening_hours );
                            $sth->bindValue(':languages', $languages );
                            $sth->bindValue(':img_thumb', $img_original );
                            $sth->bindValue(':img_gallery', $img_gallery );
                            $sth->bindValue(':status', $status );

                        $sth->execute();

                    //insert calendar
                    if ( count($calendar) != 0 ) {

                        $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES ";


                            for($c=0; $c<count($calendar); $c++) {
                                $test = "'".$id_event."'";

                                if ( $calendar[$c]['date'] != "" or $calendar[$c]['date'] != null ) {
                                    $test .= ', "'.trim(date($calendar[$c]['date'])).'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_start'] != "" or $calendar[$c]['time_start'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_start'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_end'] != "" or $calendar[$c]['time_end'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_end'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_doors'] != "" or $calendar[$c]['time_doors'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_doors'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }

                                $test_array = explode(", ", $test);
                                // echo '<pre>';
                                // var_dump($test_array);

                                $insert_calendar .= " ( ". implode(", ", $test_array) ." ),";           
                            }

                            $insert_calendar = substr($insert_calendar, 0, -1);

                        $sth2 = $this->db->prepare($insert_calendar);
                        $sth2->execute();
                    }

                    $results[] = [
                        'id' => $id_event,
                        'title' => $title,
                        'cat' => $event_category,
                    ];
                    
                }); // crawler

            }; //foreach concerts

        };
        dump($results);

    } // end function concerts

    public function shows(Request $request, Response $response)
    {
        // delete all events beginning with 'show_'
                $delete_shows = "DELETE FROM `agenda.brussels_bxl` WHERE `id_event` LIKE 'b_show_%'";
                $del = $this->db->prepare($delete_shows);
                $del->execute();

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $show_urls = [
            "https://agenda.brussels/en/spectacles/dance.html",
            "https://agenda.brussels/en/show/opera-and-operetta.html",
            "https://agenda.brussels/en/spectacles/musical-comedy.html"
        ];

        $id_count = 0; 
        $id_event = "";

        foreach (range(1, 2) as $x) {

            foreach( $show_urls as $show_url ) {

                $crawler = $client->request('GET', $show_url . "?page=$x");

                $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                    for($i = $id_count; $i <= (count($results)); $i++) {
                        $id_count++;
                        $id_event = 'b_show_' . $id_count;
                    }

                    // Events list
                    $img_thumb = $agendaNode->filter("img")->attr('src');
                    $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                    $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                    $link = $agendaNode->filter("a")->attr('href');
                    

                        // Event page
                        $client = new Client;
                        $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                        $crawler = $client->request('GET', "https://agenda.brussels" . str_replace("\/", '/', $link) );


                            $title = str_replace("/", " - ", $crawler->filter("h1.object__title")->text() );
                           
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

                            $date = null;
                            $date_start = NULL;
                            $date_end = NULL;
                            $calendar = [];

                            $gallery_img = [];

                            $description = str_replace( '<p><i>The content of this event is not available in your language.</i></p>', '', $crawler->filter('.object__text')->html() );

                            $country = 'Belgium';
                            $region = 'Brussels Capital Region';

                            
                            if ( strpos( $crawler->filter("h4.properties__title")->text(), 'Dance' ) !== false ) {
                                $event_category = 'dance';
                            }
                            if ( strpos( $crawler->filter("h4.properties__title")->text(), 'Opera' ) !== false ) {
                                $event_category = 'opera';
                            }
                            if ( strpos( $crawler->filter("h4.properties__title")->text(), 'Musical comedy' ) !== false ) {
                                $event_category = 'theater';
                            }


                            $event_type = 'show';
                            $status = 1;
                            $event_subtitle = '';
                            $event_intro = '';
                            $parthners = '';

                            $additional_infos = "";
                            $opening_hours = "";
                            $languages = "";

                            if( $crawler->filter(".properties__item--languages ul.list--properties li.list__item") !== NULL ) {
                                for($j=0; $j<$crawler->filter(".properties__item--languages ul.list--properties li.list__item")->count(); $j++) {
                                    $languages .= trim( $crawler->filter(".properties__item--languages ul.list--properties li.list__item")->eq($j)->text() ) . '<br/>';
                                }
                            }

                            $img_gallery = '';


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

                        if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                            for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                    $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                                }
                            }

                            $img_gallery = implode(", ",$gallery_img);
                        }

                        

                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = NULL;
                            $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $crawler->filter("h2.object__date")->text())));
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



                    //dates -> Calendar
                    if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                        
                        for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                            if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->eq($i)->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));

                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(0)->text();
                                            $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(1)->text();
                                            $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));
                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(2)->text();
                                            $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                }

                            }

                            if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                    $opening_hours .= trim( $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() );

                                }
                            }

                        }
                        
                    }

                    // insert event
                    $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
                        $sth = $this->db->prepare($insert_event);

                            $sth->bindValue(':id_event', $id_event);
                            $sth->bindValue(':country', $country);
                            $sth->bindValue(':region', $region);
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
                            $sth->bindValue(':additional_info', $additional_infos );
                            $sth->bindValue(':opening_hours', $opening_hours );
                            $sth->bindValue(':languages', $languages );
                            $sth->bindValue(':img_thumb', $img_original );
                            $sth->bindValue(':img_gallery', $img_gallery );
                            $sth->bindValue(':status', $status );

                        $sth->execute();

                    //insert calendar
                    if ( count($calendar) != 0 ) {

                        $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES ";


                            for($c=0; $c<count($calendar); $c++) {
                                $test = "'".$id_event."'";

                                if ( $calendar[$c]['date'] != "" or $calendar[$c]['date'] != null ) {
                                    $test .= ', "'.trim(date($calendar[$c]['date'])).'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_start'] != "" or $calendar[$c]['time_start'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_start'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_end'] != "" or $calendar[$c]['time_end'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_end'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_doors'] != "" or $calendar[$c]['time_doors'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_doors'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }

                                $test_array = explode(", ", $test);
                                // echo '<pre>';
                                // var_dump($test_array);

                                $insert_calendar .= " ( ". implode(", ", $test_array) ." ),";           
                            }

                            $insert_calendar = substr($insert_calendar, 0, -1);

                        $sth2 = $this->db->prepare($insert_calendar);
                        $sth2->execute();
                    }


                    $results[] = [
                        'id' => $id_event,
                        'title' => $title,
                        'gallery' => $img_gallery,
                        'cat' => $event_category,
                    ];

                });// crawler

            } // foreach url
            dump($results);
        };


    } //end function shows

    public function expo(Request $request, Response $response)
    {

        // delete all events beginning with 'show_'
                $delete_expo = "DELETE FROM `agenda.brussels_bxl` WHERE `id_event` LIKE 'b_expo_%'";
                $del = $this->db->prepare($delete_expo);
                $del->execute();

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $expo_urls = [
            "https://agenda.brussels/en/exhibitions/museums-and-art-ce.html",
            "https://agenda.brussels/en/exhibitions/art-galleries.html",
            //"https://agenda.brussels/en/highlights/starting-exhibition.html",
            "https://agenda.brussels/en/highlights/finishing-exhibitio.html"
        ];

        $id_count = 0; 
        $id_event = "";

        foreach (range(1, 2) as $x) {

            foreach( $expo_urls as $expo_url ) {

                $crawler = $client->request('GET', $expo_url . "?page=$x");

                $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                    for($i = $id_count; $i <= (count($results)); $i++) {
                        $id_count++;
                        $id_event = 'b_expo_' . $id_count;
                    }

                    // Events list
                    $img_thumb = $agendaNode->filter("img")->attr('src');
                    $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                    $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                    $link = $agendaNode->filter("a")->attr('href');


                        // Event page
                        $client = new Client;
                        $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                        $crawler = $client->request('GET', "https://agenda.brussels" . str_replace("\/", '/', $link) );


                            $title = str_replace("/", " - ", $crawler->filter("h1.object__title")->text() );
                           
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

                            $date = null;
                            $date_start = NULL;
                            $date_end = NULL;
                            $calendar = [];

                            $gallery_img = [];

                            $description = str_replace( '<p><i>The content of this event is not available in your language.</i></p>', '', $crawler->filter('.object__text')->html() );

                            $country = 'Belgium';
                            $region = 'Brussels Capital Region';

                            $event_category = 'contemporary art';

                            $event_type = 'exhibition';
                            $status = 1;
                            $event_subtitle = '';
                            $event_intro = '';
                            $parthners = '';
                            $additional_info = '';
                            $img_gallery = '';

                            $additional_infos = "";
                            $opening_hours = "";
                            $languages = "";

                            if( $crawler->filter(".properties__item--languages ul.list--properties li.list__item") !== NULL ) {
                                for($j=0; $j<$crawler->filter(".properties__item--languages ul.list--properties li.list__item")->count(); $j++) {
                                    $languages .= trim( $crawler->filter(".properties__item--languages ul.list--properties li.list__item")->eq($j)->text() ) . '<br/>';
                                }
                            }


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

                            if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                                for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                    if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                        $gallery_img[] = $crawler->filter('img.gallery__thumb')->eq($i)->attr('src');
                                    }
                                }

                                $img_gallery = implode(", ",$gallery_img);
                            }

                        

                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = NULL;
                            $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $crawler->filter("h2.object__date")->text())));
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



                    //dates -> Calendar
                    if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                        
                        for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                            if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->eq($i)->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));

                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(0)->text();
                                            $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(1)->text();
                                            $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));
                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(2)->text();
                                            $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                }

                            }

                            if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                    $opening_hours .= trim( $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() ) . '<br/>';

                                }
                            }

                        }
                        
                    }

                    if( $crawler->filter("li.properties__item--opening")->count() == 2 ) {

                        $calendar[0]["date"] = str_replace('Opening on ', '', $crawler->filter("li.properties__item--opening")->first()->filter('h5.properties__subtitle')->text());
                        $calendar[0]["date"] = date('Y-m-d', strtotime( $calendar[0]["date"] ));

                        if( $crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->count() == 2 ) {

                            if ( strpos($crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->first()->text(), 'Start') !== false ) { 
                                $calendar[0]["time_start"] = $crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->first()->text();
                                $calendar[0]["time_start"] = str_replace(array('Start: ', 'AM', 'PM'), '', $calendar[0]["time_start"]);

                                $calendar[0]["time_end"] = $crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->last()->text();
                                $calendar[0]["time_end"] = str_replace(array('End: ', 'AM', 'PM'), '', $calendar[0]["time_end"]);

                                $calendar[0]["time_doors"] = NULL;
                            }

                            if( strpos($crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->first()->text(), 'Doors') !== false ) {

                                $calendar[0]["time_start"] = $crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->first()->text();
                                $calendar[0]["time_start"] = str_replace(array('Doors: ', 'AM', 'PM'), '', $calendar[0]["time_doors"]);

                                $calendar[0]["time_start"] = $crawler->filter("li.properties__item--opening")->first()->filter('ul.list--properties')->filter("li.list__item")->first()->text();
                                $calendar[0]["time_start"] = str_replace(array('Start: ', 'AM', 'PM'), '', $calendar[0]["time_start"]);

                                $calendar[0]["time_end"] = NULL;
                            }

                            for($j=0; $j<$crawler->filter('table.properties__table')->filter('tr.properties__line')->count(); $j++) {

                                $opening_hours .= trim( $crawler->filter("li.properties__item--opening")->last()->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() ) . '<br/>';

                            }
                        }
                            
                    }

                    // insert event
                    $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
                        $sth = $this->db->prepare($insert_event);

                            $sth->bindValue(':id_event', $id_event);
                            $sth->bindValue(':country', $country);
                            $sth->bindValue(':region', $region);
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
                            $sth->bindValue(':additional_info', $additional_infos );
                            $sth->bindValue(':opening_hours', $opening_hours );
                            $sth->bindValue(':languages', $languages );
                            $sth->bindValue(':img_thumb', $img_original );
                            $sth->bindValue(':img_gallery', $img_gallery );
                            $sth->bindValue(':status', $status );

                        $sth->execute();

                    //insert calendar
                    if ( count($calendar) != 0 ) {

                        $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES ";


                            for($c=0; $c<count($calendar); $c++) {
                                $test = "'".$id_event."'";

                                if ( $calendar[$c]['date'] != "" or $calendar[$c]['date'] != null ) {
                                    $test .= ', "'.trim(date($calendar[$c]['date'])).'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_start'] != "" or $calendar[$c]['time_start'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_start'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_end'] != "" or $calendar[$c]['time_end'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_end'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_doors'] != "" or $calendar[$c]['time_doors'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_doors'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }

                                $test_array = explode(", ", $test);
                                // echo '<pre>';
                                // var_dump($test_array);

                                $insert_calendar .= " ( ". implode(", ", $test_array) ." ),";           
                            }

                            $insert_calendar = substr($insert_calendar, 0, -1);

                        $sth2 = $this->db->prepare($insert_calendar);
                        $sth2->execute();
                    }


                    $results[] = [
                        'id' => $id_event,
                        'title' => $title,
                        'oh' => $opening_hours,
                        'lan' => $languages,
                        'gallery' => $img_gallery,
                    ];

                });// crawler

            } // foreach url
            dump($results);
        };


    } // end function expo

    public function theater(Request $request, Response $response)
    {

        // delete all events beginning with 'show_'
                $delete_thea = "DELETE FROM `agenda.brussels_bxl` WHERE `id_event` LIKE 'b_thea_%'";
                $del = $this->db->prepare($delete_thea);
                $del->execute();

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $id_count = 0; 
        $id_event = "";

        $theater_urls = [
            "https://agenda.brussels/en/theatre/theater.html",
            "https://agenda.brussels/en/theatre/musical-theatre.html",
            "https://agenda.brussels/en/theatre/tales.html",
            "https://agenda.brussels/en/theatre/humour.html",
            "https://agenda.brussels/en/theatre/puppet-show.html",
        ];

        foreach (range(1, 2) as $x) {

            foreach( $theater_urls as $theater_url ) {

                $crawler = $client->request('GET', $theater_url . "?page=$x");

                $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                    for($i = $id_count; $i <= (count($results)); $i++) {
                        $id_count++;
                        $id_event = 'b_thea_' . $id_count;
                    }

                    // Events list
                    $img_thumb = $agendaNode->filter("img")->attr('src');
                    $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                    $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                    $link = $agendaNode->filter("a")->attr('href');
                    

                        // Event page
                        $client = new Client;
                        $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                        $crawler = $client->request('GET', "https://agenda.brussels" . str_replace("\/", '/', $link) );


                            $title = str_replace("/", " - ", $crawler->filter("h1.object__title")->text() );
                           
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

                            $date = null;
                            $date_start = null;
                            $date_end = null;
                            $calendar = [];

                            $gallery_img = [];

                            $description = str_replace( '<p><i>The content of this event is not available in your language.</i></p>', '', $crawler->filter('.object__text')->html() );

                            $country = 'Belgium';
                            $region = 'Brussels Capital Region';

                            $event_category = 'theater';
                            $event_type = 'show';
                            
                            $status = 1;
                            $event_subtitle = '';
                            $event_intro = '';
                            $parthners = '';
                            $img_gallery = implode(",",$gallery_img);

                            $additional_infos = "";
                            $opening_hours = "";
                            $languages = "";

                            if( $crawler->filter(".properties__item--languages ul.list--properties li.list__item") !== NULL ) {
                                for($j=0; $j<$crawler->filter(".properties__item--languages ul.list--properties li.list__item")->count(); $j++) {
                                    $languages .= trim( $crawler->filter(".properties__item--languages ul.list--properties li.list__item")->eq($j)->text() ) . '<br/>';
                                }
                            }


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

                        if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                            for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                                if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                    $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                                }
                            }
                        }

                        

                        if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                            $date_start = NULL;
                            //$date_end .= date('Y-m-d', strtotime($crawler->filter("h2.object__date")->text()));
                            $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $crawler->filter("h2.object__date")->text())));
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



                    //dates -> Calendar
                    if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                        
                        for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                            if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->eq($i)->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));

                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(0)->text();
                                            $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(1)->text();
                                            $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));
                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(2)->text();
                                            $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                }

                            }

                            if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                    $opening_hours .= trim( $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() );

                                }
                            }

                        }
                        
                    }

                    // insert event
                    $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
                        $sth = $this->db->prepare($insert_event);

                            $sth->bindValue(':id_event', $id_event);
                            $sth->bindValue(':country', $country);
                            $sth->bindValue(':region', $region);
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
                            $sth->bindValue(':additional_info', $additional_infos );
                            $sth->bindValue(':opening_hours', $opening_hours );
                            $sth->bindValue(':languages', $languages );
                            $sth->bindValue(':img_thumb', $img_original );
                            $sth->bindValue(':img_gallery', $img_gallery );
                            $sth->bindValue(':status', $status );

                        $sth->execute();

                    //insert calendar
                    if ( count($calendar) != 0 ) {

                        $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES ";


                            for($c=0; $c<count($calendar); $c++) {
                                $test = "'".$id_event."'";

                                if ( $calendar[$c]['date'] != "" or $calendar[$c]['date'] != null ) {
                                    $test .= ', "'.trim(date($calendar[$c]['date'])).'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_start'] != "" or $calendar[$c]['time_start'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_start'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_end'] != "" or $calendar[$c]['time_end'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_end'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_doors'] != "" or $calendar[$c]['time_doors'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_doors'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }

                                $test_array = explode(", ", $test);
                                // echo '<pre>';
                                // var_dump($test_array);

                                $insert_calendar .= " ( ". implode(", ", $test_array) ." ),";           
                            }

                            $insert_calendar = substr($insert_calendar, 0, -1);

                        $sth2 = $this->db->prepare($insert_calendar);
                        $sth2->execute();
                    }


                    $results[] = [
                        'id' => $id_event,
                        'date' => $date,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'title' => $title,
                        'calendar' => $calendar,
                        'info' => $additional_infos,
                        'text' => $description,
                        'insert' => $insert_calendar,
                    ];

                });// crawler

            } // foreach url
            dump($results);
        };

    } // end function theater

    public function cinema(Request $request, Response $response)
    {
        // delete all events beginning with 'show_'
                $delete_cine = "DELETE FROM `agenda.brussels_bxl` WHERE `id_event` LIKE 'b_cine_%'";
                $del = $this->db->prepare($delete_cine);
                $del->execute();

        $config = [
            'verify' => false,
        ];

        $client = new Client;
        $client->setClient(new \GuzzleHttp\Client($config));

        $id_count = 0; 
        $id_event = "";

        $cine_url = "https://agenda.brussels/en/cinema/cine-club.html";

        foreach (range(1, 2) as $x) {

            $crawler = $client->request('GET', $cine_url . "?page=$x");

            $crawler->filter('ul.list--agenda li.list__item')->each(function ($agendaNode) use (&$results) {

                for($i = $id_count; $i <= (count($results)); $i++) {
                    $id_count++;
                    $id_event = 'b_cine_' . $id_count;
                }

                // Events list
                $img_thumb = $agendaNode->filter("img")->attr('src');
                $img_thumb == "/front/images/placeholder.png" ? $img_thumb = null : $img_thumb;
                $img_original = substr($img_thumb, 0, strpos($img_thumb, "?"));

                $link = $agendaNode->filter("a")->attr('href');
                

                    // Event page
                    $client = new Client;
                    $client->setClient(new \GuzzleHttp\Client(['verify' => false]));
                    $crawler = $client->request('GET', "https://agenda.brussels" . str_replace("\/", '/', $link) );


                        $title = str_replace("/", " - ", $crawler->filter("h1.object__title")->text() );
                       
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

                        $date = null;
                        $date_start = null;
                        $date_end = null;
                        $calendar = [];

                        $gallery_img = [];

                        $description = str_replace( '<p><i>The content of this event is not available in your language.</i></p>', '', $crawler->filter('.object__text')->html() );

                        $country = 'Belgium';
                        $region = 'Brussels Capital Region';

                        $event_category = 'cinema';
                        $event_type = 'screening';
                        
                        $status = 1;
                        $event_subtitle = '';
                        $event_intro = '';
                        $parthners = '';
                        $img_gallery = implode(",",$gallery_img);

                        $additional_infos = "";
                        $opening_hours = "";
                        $languages = "";

                        if( $crawler->filter(".properties__item--languages ul.list--properties li.list__item") !== NULL ) {
                            for($j=0; $j<$crawler->filter(".properties__item--languages ul.list--properties li.list__item")->count(); $j++) {
                                $languages .= trim( $crawler->filter(".properties__item--languages ul.list--properties li.list__item")->eq($j)->text() ) . '<br/>';
                            }
                        }


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

                    if ( $crawler->filter('.gallery__item')->count() > 0 ) {

                        for( $i=0; $i<$crawler->filter('.gallery__item')->count(); $i++ ) {

                            if( strpos($crawler->filter('.gallery__item a')->eq($i)->attr('class'), 'gallery__link--video' ) == false ) {
                                $gallery_img[] = $crawler->filter('.gallery__item a.gallery__link img')->eq($i)->attr('src');
                            }
                        }
                    }

                    

                    if ( strpos( $crawler->filter("h2.object__date")->text(), 'Until' ) !== false ) {
                        $date_start = NULL;
                        //$date_end .= date('Y-m-d', strtotime($crawler->filter("h2.object__date")->text()));
                        $date_end = date('Y-m-d', strtotime(str_replace('Until', '', $crawler->filter("h2.object__date")->text())));
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



                    //dates -> Calendar
                    if( $crawler->filter("li.properties__item--opening h5")->count() > 0 ) {
                        
                        for($i=0; $i<$crawler->filter("li.properties__item--opening h5")->count(); $i++) {

                            if( $crawler->filter("li.properties__item--opening h5")->first()->text() !== "Dates" ) {

                                $calendar[$i]["date"] = date('Y-m-d', strtotime($crawler->filter("li.properties__item--opening h5")->eq($i)->text()));

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 1 ) {
                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                        $calendar[$i]["time_end"] = NULL;
                                    }  
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 2 ) {

                                    if ( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Start') !== false ) { 
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                        $calendar[$i]["time_doors"] = NULL;
                                    }

                                    if( strpos($crawler->filter('tr.properties__line')->first()->text(), 'Doors') !== false ) {
                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->first()->text();
                                        $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));

                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->last()->text();
                                        $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));

                                        $calendar[$i]["time_end"] = NULL;
                                    }
                                }

                                if( $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->count() == 3 ) {

                                        $calendar[$i]["time_doors"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(0)->text();
                                            $calendar[$i]["time_doors"] = date("H:i", strtotime( str_replace('Doors', '', $calendar[$i]["time_doors"]) ));
                                        $calendar[$i]["time_start"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(1)->text();
                                            $calendar[$i]["time_start"] = date("H:i", strtotime( str_replace('Start', '', $calendar[$i]["time_start"]) ));
                                        $calendar[$i]["time_end"] = $crawler->filter('table.properties__table')->eq($i)->filter("tr.properties__line")->eq(2)->text();
                                            $calendar[$i]["time_end"] = date("H:i", strtotime( str_replace('End', '', $calendar[$i]["time_end"]) ));

                                }

                            }

                            if( $crawler->filter("li.properties__item--opening h5")->last()->text() == "Timetable" ) {
                                for($j=0; $j<$crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->count(); $j++) {

                                    $opening_hours .= trim( $crawler->filter('table.properties__table')->last()->filter('tr.properties__line')->eq($j)->text() );

                                }
                            }

                        }
                        
                    }

                if ( $venue_name !== 'Kinépolis') {

                    // insert event
                    $insert_event = "REPLACE INTO `agenda.brussels_bxl`(`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
                        $sth = $this->db->prepare($insert_event);

                            $sth->bindValue(':id_event', $id_event);
                            $sth->bindValue(':country', $country);
                            $sth->bindValue(':region', $region);
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
                            $sth->bindValue(':additional_info', $additional_infos );
                            $sth->bindValue(':opening_hours', $opening_hours );
                            $sth->bindValue(':languages', $languages );
                            $sth->bindValue(':img_thumb', $img_original );
                            $sth->bindValue(':img_gallery', $img_gallery );
                            $sth->bindValue(':status', $status );

                        $sth->execute();

                    //insert calendar
                    if ( count($calendar) != 0 ) {

                        $insert_calendar = "REPLACE INTO `agenda.brussels_bxl_calendar`(`event_id`, `date`, `time_doors`, `time_start`, `time_end`) VALUES ";


                            for($c=0; $c<count($calendar); $c++) {
                                $test = "'".$id_event."'";

                                if ( $calendar[$c]['date'] != "" or $calendar[$c]['date'] != null ) {
                                    $test .= ', "'.trim(date($calendar[$c]['date'])).'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_start'] != "" or $calendar[$c]['time_start'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_start'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_end'] != "" or $calendar[$c]['time_end'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_end'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }
                                if ( $calendar[$c]['time_doors'] != "" or $calendar[$c]['time_doors'] != null ) {
                                    $test .= ', "'.$calendar[$c]['time_doors'].'"';
                                }
                                else {
                                    $test .= ', ""';
                                }

                                $test_array = explode(", ", $test);
                                // echo '<pre>';
                                // var_dump($test_array);

                                $insert_calendar .= " ( ". implode(", ", $test_array) ." ),";           
                            }

                            $insert_calendar = substr($insert_calendar, 0, -1);

                        $sth2 = $this->db->prepare($insert_calendar);
                        $sth2->execute();
                    }

                    $results[] = [
                        'id' => $id_event,
                        'date' => $date,
                        'date_start' => $date_start,
                        'date_end' => $date_end,
                        'title' => $title,
                        'calendar' => $calendar,
                        'info' => $additional_infos,
                        'text' => $description,
                        'insert' => $insert_calendar,
                    ];

                } // if not Kinépolis




            });// crawler


        dump($results);
        };
    }

}

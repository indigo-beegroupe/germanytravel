<?php

function insert($table_name, $fileds_event, $fields_calendar){


    // insert event
    $insert_event = "REPLACE INTO". $table_name ." (`id_event`, `country`, `region`, `postal_code`, `location_name`, `street_complete`, `venue_name`, `event_category`, `event_type`, `event_title`, `event_subtitle`, `event_intro`, `event_description`, `price`, `date`, `date_start`, `date_end`, `promoter`, `parthners`, `additional_info`, `opening_hours`, `languages`, `img_thumb`, `img_gallery`, `status`) VALUES (:id_event, :country, :region, :postal_code, :location_name, :street_complete, :venue_name, :event_category, :event_type, :event_title, :event_subtitle, :event_intro, :event_description, :price, :dates, :date_start, :date_end, :promoter, :parthners, :additional_info, :opening_hours, :languages, :img_thumb, :img_gallery, :status )";
                
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


}
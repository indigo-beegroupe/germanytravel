<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Facades\Cronos;
use App\Facades\Str;
use Goutte\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

class DeleteOldController extends Controller
{

    public function index(Request $request, Response $response)
    {

        $delete = "DELETE FROM `events` WHERE (`events`.date < CURDATE() AND `events`.date != '0000-00-00' ) OR ( `events`.date_end < CURDATE() AND `events`.date_end != '0000-00-00' )";

        $sth = $this->db->prepare($delete);
        $sth->execute();

    }

}

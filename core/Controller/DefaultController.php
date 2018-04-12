<?php

namespace Forum9000\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {
    /**
     * Default route used to look up a URL alias for an unknown path.
     */
    function handle_alias(Request $req, $path_alias="") {
        return new Response($path_alias);
    }
}

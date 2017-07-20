<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations as Rest;

use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class MedicinesStorageRESTController extends FOSRestController
{

    /**
     * @Rest\Get("/api/medicines/{miasto}/{nazwa}")
     */
    public function getMedicinesAction( $miasto , $nazwa ){

            //$accessor = PropertyAccess::createPropertyAccessor();
            $gm = $this->getDoctrine()->getManager();
            $prod = $gm->getRepository('AppBundle:Product')
                ->findBy(array('name' => $nazwa));
            $loc = $gm->getRepository('AppBundle:Location');
            $data = $loc->findBy(
                array('town' => $miasto, 'idProduct' => $prod),
                array('price' => 'ASC')
            );


            //echo $data[0];

        //$test = $accessor->getValue($data,'[0]');
        //$test2=$test->getTown();
        //$encoders = array(new XmlEncoder(), new JsonEncoder());
        //$normalizers = array(new ObjectNormalizer());

        //$serializer = new Serializer($normalizers, $encoders);

        //$jsonContent = $serializer->serialize($data, 'json');
        //echo $jsonContent;
        //$response = new JsonResponse();
        //$response->setData($jsonContent);

        /*if ($data === null) {
            return new View("Not found", Response::HTTP_NOT_FOUND);
        }*/
        return $data;

        /*$response = new Response($serializer->serialize($data, 'json'));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;*/

    }

}

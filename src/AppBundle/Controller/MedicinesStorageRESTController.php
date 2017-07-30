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
     * @Rest\Get("/api/medicines/{town}/{name}")
     */
    public function getMedicinesAction( $town , $name ){

            //$accessor = PropertyAccess::createPropertyAccessor();
            $gm = $this->getDoctrine()->getManager();
            $prod = $gm->getRepository('AppBundle:Product')
                ->findBy(array('name' => $name));
            $loc = $gm->getRepository('AppBundle:Location');
            $data = $loc->findBy(
                array('town' => $town, 'idProduct' => $prod),
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

    /**
     * @Rest\Get("/api/search/{qr}")
     */
    public function searchQrAction($qr){
        $gm = $this->getDoctrine()->getManager();
        $prod = $gm->getRepository('AppBundle:Product')->findBy(array('qrCode'=>$qr));

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response($serializer->serialize($prod, 'json'));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * @Rest\Post("/api/add_product")
     */
    public function addProductAction(Request $request){
        $product = new Product();
        $data = json_decode($request->getContent(),true);
        $form = $this->get('form.factory')->createNamed('',new ProductType(),$product);
        $form->submit($data);
        if($form->isValid()){
            $gm = $this->getDoctrine()->getManager();
            $gm->persist($product);
            $gm->flush();

            $response = new Response();
            $response->setContent(json_encode(array(
                'data' => 'succes',
                'idProduct' => $product->getIdProduct(),
            )));
            return $response;
        }

        $response = new Response();
        $response->setContent(json_encode(array(
            'data' => 'fail',
        )));
        return $response;
    }

    /**
     * @Rest\Post("/api/add_location")
     */
    public function addLocationAction(Request $request){
        $location = new Location();
        $data = json_decode($request->getContent(),true);
        $form = $this->get('form.factory')->createNamed('',new LocationType(),$location);
        $form->submit($data);
        if($form->isValid()){
            $gm = $this->getDoctrine()->getManager();
            $gm->persist($location);
            $gm->flush();

            $response = new Response();
            $response->setContent(json_encode(array(
                'data' => 'succes',
                //'id_loc' => $location->getIdLocation(),
            )));
            return $response;
        }

        $response = new Response();
        $response->setContent(json_encode(array(
            'data' => 'fail',
        )));
        return $response;
    }

    /**
     * @Rest\Get("/api/medicines2/{town}/{name}")
     */
    public function getMedicines2Action( $town , $name ){

        //$accessor = PropertyAccess::createPropertyAccessor();
        $gm = $this->getDoctrine()->getManager();
        $prod = $gm->getRepository('AppBundle:Product')
            ->findBy(array('name' => $name));
        $loc = $gm->getRepository('AppBundle:Location');
        $data = $loc->findBy(
            array('town' => $town, 'idProduct' => $prod),
            array('price' => 'ASC')
        );


        //select u.username, l.town, l.street, l.price, p.name, p.qr_code
        // from user as u, location as l, product as p
        // where u.id_user = l.id_user and p.id_product = l.id_product and l.town = 'kraków' and p.name = 'apap'



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

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Entity\Product;
use AppBundle\Form\Type\ProductType;
use AppBundle\Form\Type\LocationType;
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
     * @Rest\Get("/api/medicines2/{town}/{name}")
     */
    public function getMedicines2Action( $town , $name ){
            $gm = $this->getDoctrine()->getManager();
            $prod = $gm->getRepository('AppBundle:Product')
                ->findBy(array('name' => $name));
            $loc = $gm->getRepository('AppBundle:Location');
            $data = $loc->findBy(
                array('town' => $town, 'idProduct' => $prod),
                array('price' => 'ASC')
            );
        return $data;
    }

    /**
     * @Rest\Get("/api/medicines/{town}/{name}")
     */
    public function getMedicinesAction($town,$name){
        $em = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('SELECT loc.town, loc.street, loc.price, pr.name,u.id_user, u.username FROM location loc
                      LEFT JOIN product pr ON pr.id_product = loc.id_product
                      LEFT JOIN user as u on u.id_user = loc.id_user
                      WHERE loc.town =:town
                      AND pr.name =:name
                      ORDER BY loc.price');
        $em->bindValue('town',$town);
        $em->bindValue('name',$name);
        $em->execute();
        $data = $em->fetchAll();
        //$data2 = json_encode($data);

        //return new Response($data2);
        return $data;
    }

    /**
     * @Rest\Get("/api/search2/{qr}")
     */
    public function search2QrAction($qr){
        $gm = $this->getDoctrine()->getManager();
        $prod = $gm->getRepository('AppBundle:Product')->findBy(array('qrCode'=>$qr));
        return $prod;
    }

    /**
     * @Rest\Get("/api/search/{qr}")
     */
    public function searchAction($qr){
        $em = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('SELECT pr.name, pr.qr_code, pr.id_product FROM product pr
                      WHERE pr.qr_code =:qr');
        $em->bindValue('qr',$qr);
        $em->execute();
        $data = $em->fetchAll();
        //$data2 = json_encode($data);

        //return new Response($data2);
        return $data;
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
                'id_product' => $product->getIdProduct(),
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

}

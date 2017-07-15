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

use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * @Prefix("api/product")
 * @NamePrefix("product")
 * Following annotation is redundant, since FosRestController implements ClassResourceInterface
 * so the Controller name is used to define the resource. However with this annotation its
 * possible to set the resource to something else unrelated to the Controller name
 * @RouteResource("Product")
 */
class MedicinesStorageRESTController extends FOSRestController
{

    /**
     * Get the list of articles.
     *
     * @return array data
     *
     * @View()
     * @QueryParam(name="miasto", requirements="\d+", default="1", description="Page of the overview.")
     * @QueryParam(name="nazwa", requirements="\d+", default="1", description="Page of the overview.")
     */
    public function getMedicinesAction($miasto,$nazwa)
    {

        //$accessor = PropertyAccess::createPropertyAccessor();
        $gm = $this->getDoctrine()->getManager();
        $prod = $gm->getRepository('AppBundle:Product')->findBy(array('name'=>$nazwa));
        $loc = $gm->getRepository('AppBundle:Location');
        $data = $loc->findBy(
            array('town'=>$miasto,'idProduct'=>$prod),
            array('price'=>'ASC')
        );
        //$test = $accessor->getValue($data,'[0]');
        //$test2=$test->getTown();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        //$jsonContent = $serializer->serialize($data, 'json');
        //echo $jsonContent;
        //$response = new JsonResponse();
        //$response->setData($jsonContent);

        $response = new Response($serializer->serialize($data, 'json'));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;

    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Entity\Product;
use AppBundle\Form\Type\ProductType;
use AppBundle\Form\Type\LocationType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'base_dir' => realpath($this->container->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ));
    }

    /**
     * @Route("/api/search/{miasto}/{nazwa}")
     *
     *
     */
    public function searchAction($miasto,$nazwa){

        if (false === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }


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


        //return array('entities'=>$entities);


    }

    /**
     * @Route("/search/{qr}")
     *
     *
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
     * @Route("/add_product")
     *
     *
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
     * @Route("/add_location")
     *
     *
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

    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class,$user);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }
    }


    /**
     * @Route("/admin")
     */
    public function adminAction(){
        return new Response('<html><body>Admin padge!</body></html>');
    }

    /**
     * @Route("/test/test")
     */
    public function testAction(){
        return new Response('dziala');
    }



    /**
     * @Route("/login", name="login_route")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'default/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }
    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }

    /**
     * @Route("/addClient", name="add_client")
     */
    public function addClientAction()
    {
        $clientManager = $this->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris(array('http://adam.wroclaw.pl'));
        $client->setAllowedGrantTypes(array('token', 'authorization_code'));
        $clientManager->updateClient($client);
        $output = sprintf("Added client with id: %s secret: %s",$client->getPublicId(),$client->getSecret());
        return new Response($output);

        //Added client with id: 1_5orqn0eet08wsk04ws40g0444cgok0kg04sowscc0s8sg4gs4s secret: 2aleypjq5kisgcg4kws040coskkkosgkwkc8s0c04kwksgsoc8
    }

}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Location;
use AppBundle\Entity\Product;
use AppBundle\Entity\User;
use AppBundle\Form\Type\ProductType;
use AppBundle\Form\Type\LocationType;
use AppBundle\Form\Type\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\ResultSetMapping;
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
     * @Route("/search/{miasto}/{nazwa}")
     *
     *
     */
    public function searchAction($miasto,$nazwa){

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
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($data, 'json');
        $response = new JsonResponse();
        $response->setData($jsonContent);
         $response = new Response($serializer->serialize($data, 'json'));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;


    }


    /**
     * @Route("/test/{miasto}/{nazwa}")
     *
     *
     */
    public function test2Action($miasto,$nazwa){
        $em = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('SELECT loc.town, loc.street, loc.price, pr.name,u.id_user, u.username FROM location loc
                      LEFT JOIN product pr ON pr.id_product = loc.id_product
                      LEFT JOIN user as u on u.id_user = loc.id_user
                      WHERE loc.town =:town
                      AND pr.name =:name');
        $em->bindValue('town',$miasto);
        $em->bindValue('name',$nazwa);
        $em->execute();
        $data = $em->fetchAll();
        $data2 = json_encode($data);

        return new Response($data2);
    }

    /*
     select pr.name, pr.qr_code, loc.price, loc.town, loc.street, u.username
from location as loc
left join product as pr on pr.id_product = loc.id_product
left join user as u on u.id_user = loc.id_user
where loc.town = 'kraków'
     */





    /**
     * @Route("/search/{qr}")
     *
     *
     */
    public function searchQrAction($qr){
        $gm = $this->getDoctrine()->getManager();
        $prod = $gm->getRepository('AppBundle:Product')->findBy(array('qrCode'=>$qr));
        //print_r($prod);
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response($serializer->serialize($prod, 'json'));
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');
        return $response;
    }

    /**
     * @Route("/test/{qr}")
     *
     *
     */
    public function test3Action($qr){
        $em = $this->getDoctrine()->getEntityManager()
            ->getConnection()
            ->prepare('SELECT pr.name, pr.qr_code, pr.id_product FROM product pr
                      WHERE pr.qr_code =:qr');
        $em->bindValue('qr',$qr);
        $em->execute();
        $data = $em->fetchAll();
        $data2 = json_encode($data);

        return new Response($data2);
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
     * @Route("/reg")
     *
     *
     */
    public function regAction(Request $request)
    {
        $user = new User();
        $data = json_decode($request->getContent(),true);
        $form = $this->get('form.factory')->createNamed('',new UserType(),$user);
        $form->submit($data);
        if($form->isValid()){
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $gm = $this->getDoctrine()->getManager();
            $gm->persist($user);
            $gm->flush();

            $response = new Response();
            $response->setContent(json_encode(array(
                'data' => 'succes',
                //'id_loc' => $location->getIdLocation(),
            )));
            return $response;
        }

        /* $location = new Location();
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
        }*/

        $response = new Response();
        $response->setContent(json_encode(array(
            'data' => 'fail',
        )));
        return $response;
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


    public function loginAction(Request $request)
        //@Route("/login", name="login_route")
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

    public function loginCheckAction()
        //@Route("/login_check", name="login_check")
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    public function logoutAction()
//@Route("/logout", name="logout")
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
        $client->setAllowedGrantTypes(array('password','refresh_token'));
        $clientManager->updateClient($client);
        $output = sprintf("Added client with id: %s secret: %s",$client->getPublicId(),$client->getSecret());
        return new Response($output);

        //Added client with id: 1_5orqn0eet08wsk04ws40g0444cgok0kg04sowscc0s8sg4gs4s secret: 2aleypjq5kisgcg4kws040coskkkosgkwkc8s0c04kwksgsoc8

        //Added client with id: 5_21vemuwjz78kww8sscccgkwg84ogs4c8gw0scg4wogsgkcg444 secret: ymotev8phys8wgw4okcgosc44w4884cc80kk8w4gkk4cgwccs
    }

}

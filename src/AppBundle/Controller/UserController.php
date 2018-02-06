<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Psr\Log\LoggerInterface;

use AppBundle\Entity\User;
use AppBundle\Entity\Category;

/**
 * Handles public user requests
 * 
 */
class UserController extends Controller
{
    public $serializer;
    public $logger;
    public $em;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em){
      $encoders = array(new XmlEncoder(), new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());
      
      $this->serializer = new Serializer($normalizers, $encoders);
      $this->logger = $logger;
      $this->em = $em;

    }
    

    /**
     * @Route("/apikey")
     * @Method("GET")
     */
    public function getApikeyAction(Request $request)
    {
        $ret = [];
        $ret['messages'] = [];
        if(!array_key_exists('username', $params)){
            $ret['messages'][] = 'Username not found';
        }
        if(!array_key_exists('password', $params)){
            $ret['messages'][] = 'Password not found';
        }
        if (count($ret['messages']) > 0){
            $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
        } else {
            $user = $this->getDoctrine()->getRepository(User::class)->findByUsername($params->username);
            if($user){
                $jsonContent = $this->serializer->serialize(['apikey' => $user->apiToken], 'json');
                $response = new Response($jsonContent, Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');    
            } else {
                $response = new Response("", Response::HTTP_NOT_FOUND);
            }                     
        }
        return $response;
    }


}
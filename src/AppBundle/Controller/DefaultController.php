<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use AppBundle\Entity;
use AppBundle\Utils\DataLoader;

class DefaultController extends Controller
{
    public $serializer;
    public $dataLoader;

    public function __construct(DataLoader $dataLoader)
    {
      $encoders = array(new XmlEncoder(), new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());
      
      $this->serializer = new Serializer($normalizers, $encoders);

      $this->dataLoader = $dataLoader;
    }
    
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
   
    /**
     * @Route("/load", name="load")
     */
    public function loadAction(Request $request)
    {
        $text = '{
            "products": [
              {
                "name": "Pong",
                "category": "Games",
                "sku": "A0001",
                "price": 69.99,
                "quantity": 20
              },
              {
                "name": "GameStation 5",
                "category": "Games",
                "sku": "A0002",
                "price": 269.99,
                "quantity": 15
              },
              {
                "name": "AP Oman PC - Aluminum",
                "category": "Computers",
                "sku": "A0003",
                "price": 1399.99,
                "quantity": 10
              },
              {
                "name": "Fony UHD HDR 55\" 4k TV",
                "category": "TVs and Accessories",
                "sku": "A0004",
                "price": 1399.99,
                "quantity": 5
              }
            ],
            "users": [
              {
                "name": "Bobby Fischer",
                "email": "bobby@foo.com"
              },
              {
                "name": "Betty Rubble",
                "email": "betty@foo.com"
              }
            ]
          }';

        $this->dataLoader->load($text);
         $response = new Response(
            'ok',
            Response::HTTP_OK,
            array('content-type' => 'text/html')
         );
         return $response;
    }
}
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

use AppBundle\Entity\Category;

/**
 * Handles public category requests
 * 
 * @Route("/categories")
 */
class CategoryController extends Controller
{
    public $serializer;

    public function __construct()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @Route("/")
     * @Method("GET")
     */
    public function getAction($id)
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $jsonContent = $this->serializer->serialize($categories, 'json');
        $response = new Response($jsonContent, Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
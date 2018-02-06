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

use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Util\ProductHandler;

/**
 * Handles public product requests
 * 
 * @Route("/products")
 */
class ProductController extends Controller
{
    public $serializer;
    public $logger;
    public $em;
    public $product_handler;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, EntityManagerInterface $product_handler){
      $encoders = array(new XmlEncoder(), new JsonEncoder());
      $normalizers = array(new ObjectNormalizer());
      
      $this->serializer = new Serializer($normalizers, $encoders);
      $this->logger = $logger;
      $this->em = $em;
      $this->product_handler = $product_handler;

    }
    

    /**
     * @Route("/{id}", defaults={"id" = 0})
     * @Method("GET")
     */
    public function getAction($id)
    {
        if($id == 0){
            // List
            $products = $this->getDoctrine()->getRepository(Product::class)->findAll();
            $jsonContent = $this->serializer->serialize($products, 'json');
            $response = new Response($jsonContent, Response::HTTP_OK);
            $response->headers->set('Content-Type', 'application/json');
        } else {
            // GET
            $product = $this->getDoctrine()->getRepository(Product::class)->find($id);
            if(!$product){
                $response = new Response("", Response::HTTP_NOT_FOUND);
            } else {
                $jsonContent = $this->serializer->serialize($product, 'json');
                $response = new Response($jsonContent, Response::HTTP_OK);
                $response->headers->set('Content-Type', 'application/json');
            }
        }
        return $response;
    }

    /**
     * @Route("/admin/")
     * @Method("POST")
     */
    public function postAction(Request $request)
    {
        $params = array();
        $json_text = $request->getContent();
        $params = json_decode($json_text, true); 

        $ret = $this->product_handler->create($params);

        $response = new Response(json_encode($ret), $ret['retcode']);
        return $response;
    }


    /**
     * @Route("/admin")
     * @Method("PUT")
     */
    public function putAction(Request $request)
    {
        $params = array();
        $json_text = $request->getContent();

        $params = json_decode($json_text, true); 
        //Validate input
        $ret = [];
        $ret['messages'] = [];
        if(!array_key_exists('id', $params)){
            $ret['messages'][] = 'Id not found';
        }
        if(!array_key_exists('name', $params)){
            $ret['messages'][] = 'Name not found';
        }
        if(!array_key_exists('category', $params)){
            $ret['messages'][] = 'Category not found';
        } else {
            $repCategory = $this->em->getRepository(Category::class);
            $cat = $repCategory->findOrCreateByName($params['category'], $this->logger);
        }
        if(!array_key_exists('sku', $params)){
            $ret['messages'][] = 'Sku not found';
        }
        if(!array_key_exists('price', $params)){
            $ret['messages'][] = 'Price not found';
        }
        if(!array_key_exists('quantity', $params)){
            $ret['messages'][] = 'Quantity not found';
        }
        if (count($ret['messages']) > 0){
            $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
        } else {
            try
            {
                $product = $this->em->getRepository(Product::class)->find($params['id']);
                if(!$product){
                    $ret['messages'][] = 'Product not found';
                    $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
                } else {
                    $product->setName($params['name']);
                    $product->setPrice($params['price']);
                    $product->setSku($params['sku']);
                    $product->setQuantity($params['quantity']);
                    $product->setCategory($cat);
                    $product->setModifiedAt(new \Datetime("now"));
                    $this->em->persist($product);
                    $this->em->flush();        
                    $response = new Response('', Response::HTTP_OK);        
                }
            }
            catch(\Doctrine\DBAL\Exception\ConstraintViolationException $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
            }
            catch(\Exception $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $response = new Response(json_encode($ret), Response::HTTP_INTERNAL_SERVER_ERROR);            
            }
        }
        return $response;
    }


    /**
     * @Route("/admin")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        $params = array();
        $json_text = $request->getContent();

        $params = json_decode($json_text, true); 
        //Validate input
        $ret = [];
        $ret['messages'] = [];
        if(!array_key_exists('id', $params)){
            $ret['messages'][] = 'Id not found';
        }
        if (count($ret['messages']) > 0){
            $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
        } else {
            try
            {
                $product = $this->em->getRepository(Product::class)->find($params['id']);
                if(!$product){
                    $ret['messages'][] = 'Product not found';
                    $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
                } else {
                    $this->em->remove($product);
                    $this->em->flush();        
                    $response = new Response('', Response::HTTP_OK);        
                }
            }
            catch(\Doctrine\DBAL\Exception\ConstraintViolationException $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $response = new Response(json_encode($ret), Response::HTTP_BAD_REQUEST);            
            }
            catch(\Exception $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $response = new Response(json_encode($ret), Response::HTTP_INTERNAL_SERVER_ERROR);            
            }
        }
        return $response;            
    }
}
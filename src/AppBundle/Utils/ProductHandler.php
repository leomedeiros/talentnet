<?php

namespace AppBundle\Utils;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Response;

class ProductHandler
{
    public $em;
    public $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $product_handler){
        $this->em = $em;
        $this->logger = $logger;
    }
    /**
     * Undocumented function
     *
     * @param [type] $params
     * @return array
     */
    public function create($params){
        //Validate input
        $ret = [];
        $ret['messages'] = [];
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
            $ret['retcode'] = Response::HTTP_BAD_REQUEST;
        } else {
            try
            {
                $product = new Product;
                $product->setName($params['name']);
                $product->setPrice($params['price']);
                $product->setSku($params['sku']);
                $product->setQuantity($params['quantity']);
                $product->setCategory($cat);
                $product->setCreatedAt(new \Datetime("now"));
                $this->em->persist($product);
                $this->em->flush();        
                $ret['retcode'] = Response::HTTP_CREATED;
            }
            catch(\Doctrine\DBAL\Exception\ConstraintViolationException $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $ret['retcode'] = Response::HTTP_BAD_REQUEST;
            }
            catch(\Exception $excp)
            {
                $ret['messages'][] = $excp->getMessage();                
                $ret['retcode'] = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }
    }

    public function update($params){
        
    }
}
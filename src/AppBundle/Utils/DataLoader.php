<?php

namespace AppBundle\Utils;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Product;
use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DataLoader
{
    public $logger;
    public $em;
    public $encoder;

    /**
     * Injects the logger and the doctrine instance
     *
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->encoder= $encoder;
    }

    /**
     * Loads a json format text into the database
     *
     * @param [type] $txt_data
     * @return void
     */
    public function load($txt_data)
    {
        try
        {
            $this->logger->info('Setting up loader...');

            $this->logger->info('==> Truncating tables...');
            $this->em->getConnection()->query('START TRANSACTION;SET FOREIGN_KEY_CHECKS=0; TRUNCATE product; TRUNCATE category; TRUNCATE user; SET FOREIGN_KEY_CHECKS=1; COMMIT;');
            $this->logger->info('DONE!');

            $this->logger->info('==> Loading products...');
            $json_data = json_decode($txt_data);
            $repCategory = $this->em->getRepository(Category::class);
            $repCategory->logger = $this->logger;
            // Loops through the json products 
            foreach($json_data->products as $json_product){
                $this->logger->info('Creating product '.$json_product->name);
                $cat = $repCategory->findOrCreateByName($json_product->category, $this->logger);
    
                $product = new Product();
                $product->setName($json_product->name);
                $product->setPrice($json_product->price);
                $product->setSku($json_product->sku);
                $product->setQuantity($json_product->quantity);   
                $product->setCreatedAt(new \Datetime("now"));
                $product->setCategory($cat);
                $this->em->persist($product);
                $this->em->flush();
                $this->logger->info('Creating product DONE!');
            }
            $this->logger->info('Loading products...DONE!');

            $this->logger->info('==> Loading users...');
            foreach($json_data->users as $json_user){
                $this->logger->info('Creating user '.$json_user->name);                
                $user = new User();
                $user->setUsername($json_user->name);
                $user->setEmail($json_user->email);
                $encoded = $this->encoder->encodePassword($user, $json_user->email);
                $user->setPassword($encoded);
                $this->em->persist($user);
                $this->em->flush();

                $this->logger->info('Creating user...DONE!');
            }
            $this->logger->info('Loading users...DONE!');
            
            $this->logger->info('Load finished sucessfully');    
        } 
        catch(\Exception $excp)
        {
            $this->logger->error('Error while processing the file '.$excp->getMessage());
            $this->logger->error('Load finished with errors ***********');    
        }
    }
}
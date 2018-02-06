<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Mapping;
/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends \Doctrine\ORM\EntityRepository
{

    public function findOrCreateByName($name, $logger){
        $cat = $this->findOneByName($name);
        if (empty($cat) ){
            // Create a new one
            $logger->info('Creating category for name='.$name);
            $em = $this->getEntityManager();
            $cat = new Category();
            $cat->setName($name);
            $cat->setCreatedAt(new \Datetime("now", new \DateTimeZone("Canada/Eastern")));
            $em->persist($cat);
            $em->flush();            
            $logger->info('Creating category...DONE!');
        } else {
            $logger->info('Category found for name='.$name);            
        }
        return $cat;
    }
}

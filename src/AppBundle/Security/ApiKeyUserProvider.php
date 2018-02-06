<?php 
namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity;

use Psr\Log\LoggerInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    public $em;
    public $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function getUsernameForApiKey($apiKey)
    {            
        $user = $this->em->getRepository(Entity\User::class)->findOneByApiToken($apiKey);
        if($user){
            // return $user->getUsername();
            return $user;
        } else {
            return null;
        }
    }

    public function loadUserByUsername($username)
    {
        return new User(
            $username,
            null,
            array('ROLE_API')
        );
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return Entity\User::class === $class;
    }
}
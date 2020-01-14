<?php

namespace App\Repository;

use App\Entity\V2\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param string $name
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getOrCreateByName(string $name): User
    {
        $user = $this->findOneBy(['name' => $name]);
        if (!$user) {
            $user = new User($name);
            $this->_em->persist($user);
            $this->_em->flush();
        }

        return $user;
    }
}

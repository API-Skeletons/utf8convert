<?php

namespace Db\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Db\Entity;
use Zend\Crypt\Password\Bcrypt;

class Role implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $guest = new Entity\Role();
        $guest->setRoleId('guest');
        $manager->persist($guest);

        $view = new Entity\Role();
        $view->setParent($guest);
        $view->setRoleId('view');
        $manager->persist($view);

        $edit = new Entity\Role();
        $edit->setParent($view);
        $edit->setRoleId('edit');
        $manager->persist($edit);

        $administrator = new Entity\Role();
        $administrator->setParent($edit);
        $administrator->setRoleId('administrator');
        $manager->persist($administrator);

        $manager->flush();
    }
}
